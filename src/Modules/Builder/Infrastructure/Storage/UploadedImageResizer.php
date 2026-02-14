<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Storage;

use Illuminate\Http\UploadedFile;

interface UploadedImageResizer
{
    /**
     * Resize an uploaded image into a temporary file.
     *
     * Returns null when no resize is needed or processing is unavailable.
     * When resized, returns ['path' => '/tmp/..', 'extension' => 'jpg|png|webp|gif'].
     */
    public function resizeToTemp(UploadedFile $file): ?array;
}
