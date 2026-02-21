<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Jobs;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository\UserDataExportRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Models\User;
use BilliftyResumeSDK\SharedResources\Modules\User\Models\UserDataExport;
use BilliftyResumeSDK\SharedResources\Modules\User\Notifications\UserDataExportReadyNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use ZipArchive;

class GenerateUserDataExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $exportId) {}

    public function handle(UserDataExportRepository $userDataExport): void
    {
        /** @var UserDataExport|null $export */
        $export = $userDataExport->find($this->exportId);

        if (!$export || $export->status === 'expired') {
            return;
        }

        $export->update(['status' => 'processing', 'error' => null]);

        try {
            $userModelClass = config('auth.providers.users.model', User::class);
            if (!is_string($userModelClass) || !class_exists($userModelClass)) {
                $userModelClass = User::class;
            }

            /** @var User $user */
            $user = $userModelClass::query()->findOrFail($export->user_id);

            // Collect data (MVP: keep it simple and safe)
            // Replace/extend these queries based on your real models.
            $payload = [
                'meta' => [
                    'exported_at' => now()->toIso8601String(),
                    'app' => config('app.name'),
                ],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name ?? null,
                    'email' => $user->email ?? null,
                    'created_at' => optional($user->created_at)->toIso8601String(),
                ],

                // Example: if you have resumes table linked to user_id
                'resumes' => $this->getRowsByUserId('resumes', $user->id),

                // Example: subscriptions/billing (adjust to your schema)
                'subscriptions' => $this->getRowsByUserId('subscriptions', $user->id),
            ];

            $dir = 'exports';
            Storage::disk('public')->makeDirectory($dir);

            $safeName = Str::slug($user->name ?: Str::before((string) $user->email, '@'));
            if ($safeName === '') {
                $safeName = 'user';
            }

            $zipName = "{$safeName}_{$user->id}.zip";
            $zipPath = "{$dir}/{$zipName}";
            $absoluteZipPath = Storage::disk('public')->path($zipPath);

            // Create zip
            $zip = new ZipArchive();
            if ($zip->open($absoluteZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Unable to create export zip.');
            }

            // Add JSON
            $zip->addFromString('data/user_data.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            // OPTIONAL (MVP): add uploaded avatar/photo if stored locally
            // Example if you store user avatar path on $user->avatar_path
            // if (!empty($user->avatar_path) && Storage::disk('public')->exists($user->avatar_path)) {
            //     $zip->addFile(Storage::disk('public')->path($user->avatar_path), 'uploads/avatar' . '.' . pathinfo($user->avatar_path, PATHINFO_EXTENSION));
            // }

            $zip->close();

            $export->update([
                'status' => 'ready',
                'file_path' => $zipPath,
                'expires_at' => $export->expires_at ?? now()->addHours(24),
            ]);

            $downloadUrl = URL::temporarySignedRoute(
                'data-export.download',
                $export->expires_at ?? now()->addHours(24),
                ['export' => $export->id]
            );

            try {
                $user->notify(new UserDataExportReadyNotification($export->fresh(), $downloadUrl));
            } catch (\Throwable $notificationError) {
                logger()->warning('Failed to send data export email notification.', [
                    'export_id' => $export->id,
                    'user_id' => $user->id,
                    'error' => $notificationError->getMessage(),
                ]);
            }

        } catch (\Throwable $e) {
            $export->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            throw $e; // keeps queue visibility; you can remove if you want silent fail
        }
    }

    private function getRowsByUserId(string $table, int $userId): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }

        return DB::table($table)
            ->where('user_id', $userId)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }
}
