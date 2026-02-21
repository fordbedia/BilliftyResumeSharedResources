<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository\UserDataExportRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Jobs\GenerateUserDataExportJob;
use BilliftyResumeSDK\SharedResources\Modules\User\Models\UserDataExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserDataExportController extends Controller
{
    public function requestExport(Request $request, UserDataExportRepository $userDataExport)
    {
        $user = $request->user();

        // Prevent spamming exports (MVP throttling)
        $recent = $userDataExport->recent($user->id);

        if ($recent) {
            return response()->json([
                'message' => 'An export was recently requested. Please use the latest export.',
                'export' => $this->transform($recent),
            ], 202);
        }

		$export = $userDataExport->create([
            'user_id' => $user->id,
            'status' => 'queued',
            'expires_at' => now()->addHours(24),
        ]);

        GenerateUserDataExportJob::dispatch($export->id);

        return response()->json([
            'message' => 'Export request received. We are generating your data export.',
            'export' => $this->transform($export),
        ], 202);
    }

    public function latest(Request $request, UserDataExportRepository $userDataExport)
    {
        $user = $request->user();

        $export = $userDataExport->findLatest($user->id);

        return response()->json([
            'export' => $export ? $this->transform($export) : null,
        ]);
    }

    public function download(Request $request, UserDataExport $export)
    {
        $user = $request->user();

        abort_unless($export->user_id === $user->id, 403);

        if ($export->status !== 'ready' || !$export->file_path) {
            return response()->json(['message' => 'Export is not ready yet.'], 409);
        }

        if ($export->expires_at && now()->greaterThan($export->expires_at)) {
            $export->update(['status' => 'expired']);
            return response()->json(['message' => 'This export link has expired.'], 410);
        }

        if (Storage::disk('public')->exists($export->file_path)) {
            return Storage::disk('public')->download($export->file_path);
        }

        abort_unless(Storage::disk('local')->exists($export->file_path), 404);

        // Backward compatibility for older exports saved on local disk.
        return Storage::disk('local')->download($export->file_path);
    }

    private function transform(UserDataExport $export): array
    {
        $downloadUrl = null;

        if ($export->status === 'ready') {
            $downloadUrl = \URL::temporarySignedRoute(
                'data-export.download',
                $export->expires_at ?? now()->addHours(24),
                ['export' => $export->id]
            );
        }

        return [
            'id' => $export->id,
            'status' => $export->status,
            'expires_at' => optional($export->expires_at)->toIso8601String(),
            'created_at' => optional($export->created_at)->toIso8601String(),
            'download_url' => $downloadUrl,
            'error' => $export->status === 'failed' ? $export->error : null,
        ];
    }
}
