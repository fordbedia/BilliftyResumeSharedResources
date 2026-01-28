<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Jobs;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Action\GenerateResumeExportFileAction;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Mail\ResumeExportMail;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SendResumeExportEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $resumeId,
        public string $toEmail,
        public string $fileFormat, // pdf|docx
    ) {}

    public function handle(GenerateResumeExportFileAction $generate): void
    {
        $resume = Resume::query()
            ->findOrFail($this->resumeId)
            ->loadMissing(Resume::relationships());

        $resume->forceFill([
            'email_export_status' => 'processing',
            'email_export_error'  => null,
        ])->save();

        // Always expect the action to return the structured array.
        $result = $generate->handle($resume, $this->fileFormat);

        $disk = $result['disk'] ?? config('builder.export_disk', 'public');
        $path = $result['path'] ?? null;
        $absolutePath = $result['absolute_path'] ?? null;
        $downloadName = $result['filename'] ?? "resume_{$resume->getKey()}.{$this->fileFormat}";

        if (!is_string($path) || $path === '') {
            throw new \RuntimeException('GenerateResumeExportFileAction did not return a valid path.');
        }

        if (!is_string($absolutePath) || $absolutePath === '' || !is_file($absolutePath)) {
            throw new \RuntimeException("Export file not found on disk for attachment: {$absolutePath}");
        }

        try {
            Mail::to($this->toEmail)->send(
                new ResumeExportMail(
                    resume: $resume,
                    fileFormat: $this->fileFormat,
                    absoluteAttachmentPath: $absolutePath,
                    attachmentName: $downloadName,
                )
            );

            $resume->forceFill([
                'email_export_status' => 'sent',
                'email_export_error'  => null,
            ])->save();
        } catch (Throwable $e) {
            \Log::error('SendResumeExportEmailJob failed', [
                'resume_id'    => $this->resumeId,
                'to_email'     => $this->toEmail,
                'file_format'  => $this->fileFormat,
                'disk'         => $disk,
                'path'         => $path,
                'absolutePath' => $absolutePath,
                'message'      => $e->getMessage(),
            ]);

            throw $e; // keep job marked as failed
        } finally {
            /**
             * Cleanup strategy:
             * - If disk is remote (s3), the action downloaded a temp file → unlink it.
             * - Optional: delete the stored export from the disk after emailing.
             */
            $driver = config("filesystems.disks.{$disk}.driver");

            if ($driver !== 'local') {
                // This is the temp file created by the action for remote disks
                @unlink($absolutePath);
            }

            // OPTIONAL: delete the exported file from storage (S3 or local/public)
            // If you want to keep exports for download/history, REMOVE this.
            Storage::disk($disk)->delete($path);
        }
    }

    public function failed(Throwable $e): void
    {
        $resume = Resume::query()->find($this->resumeId);

        if ($resume) {
            $resume->forceFill([
                'email_export_status' => 'failed',
                'email_export_error'  => $e->getMessage(),
            ])->save();
        }
    }
}
