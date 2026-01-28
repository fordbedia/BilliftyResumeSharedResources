<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Action;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources\ResumeJsonResource;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume;
use Illuminate\Support\Facades\Storage;

class GenerateResumeExportFileAction
{
    /**
     * @return array{
     *   disk:string,
     *   path:string,
     *   absolute_path:string,
     *   filename:string
     * }
     */
    public function handle(
        Resume $resumeModel,
        string $fileFormat,              // pdf|docx
        ?string $disk = null,
        ?string $templateView = null
    ): array {
        if (!in_array($fileFormat, ['pdf', 'docx'], true)) {
            throw new \InvalidArgumentException("Invalid file format: {$fileFormat}");
        }

        $disk = $disk ?? config('builder.export_disk', 'public');
        $templateView = $templateView ?? config('builder.export_template_view', 'builder::resume');

        // JSON-Resume shaped array for the blade template
        $resume = (new ResumeJsonResource($resumeModel))->toArray(request());

        // Generate the file on the configured disk
        if ($fileFormat === 'pdf') {
            $result = app(GenerateResumePdfAction::class)->handle(
                resume: $resume,
                templateView: $templateView,
                disk: $disk
            );
        } else {
            $result = app(GenerateResumeDocxAction::class)->handle(
                resume: $resume,
                disk: $disk
            );
        }

        $path = $result['path'] ?? null;
        if (!is_string($path) || $path === '') {
            throw new \RuntimeException('Export action did not return a valid path.');
        }

        $filename = "resume_{$resumeModel->getKey()}.{$fileFormat}";
        $driver = config("filesystems.disks.{$disk}.driver");

        // Local disk → attach via absolute path
        if ($driver === 'local') {
            return [
                'disk' => $disk,
                'path' => $path,
                'absolute_path' => Storage::disk($disk)->path($path),
                'filename' => $filename,
            ];
        }

        // Remote disk (s3, etc) → download to temp and attach temp file
        $tmpBase = tempnam(sys_get_temp_dir(), 'resume_export_');
        $tmpFile = $tmpBase . '.' . $fileFormat;

        file_put_contents($tmpFile, Storage::disk($disk)->get($path));
        @unlink($tmpBase);

        return [
            'disk' => $disk,
            'path' => $path,
            'absolute_path' => $tmpFile,
            'filename' => $filename,
        ];
    }
}
