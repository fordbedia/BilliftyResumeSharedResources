<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Storage;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Storage\UploadedImageResizer;
use Illuminate\Http\UploadedFile;

class GdImageUploadResizer implements UploadedImageResizer
{
    public function __construct(
        protected int $maxWidth = 1200,
        protected int $maxHeight = 1200,
        protected int $jpegQuality = 85,
        protected int $webpQuality = 85,
        protected int $pngCompression = 6,
    ) {
    }

    public function resizeToTemp(UploadedFile $file): ?array
    {
        if (!function_exists('gd_info')) {
            return null;
        }

        $realPath = $file->getRealPath();
        if (!$realPath || !is_file($realPath)) {
            return null;
        }

        $size = @getimagesize($realPath);
        if (!$size || !isset($size[0], $size[1], $size['mime'])) {
            return null;
        }

        [$width, $height] = $size;
        $mime = strtolower((string) $size['mime']);

        if ($width <= 0 || $height <= 0) {
            return null;
        }

        $ratio = min(
            $this->maxWidth / $width,
            $this->maxHeight / $height,
            1
        );

        // No resize needed.
        if ($ratio >= 1) {
            return null;
        }

        $targetWidth = max((int) floor($width * $ratio), 1);
        $targetHeight = max((int) floor($height * $ratio), 1);

        $source = @imagecreatefromstring((string) file_get_contents($realPath));
        if ($source === false) {
            return null;
        }

        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($target === false) {
            imagedestroy($source);
            return null;
        }

        $extension = 'jpg';

        // Preserve transparency for PNG/GIF/WEBP.
        if (in_array($mime, ['image/png', 'image/gif', 'image/webp'], true)) {
            imagealphablending($target, false);
            imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
            imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $width,
            $height
        );

        $tempPath = tempnam(sys_get_temp_dir(), 'resume_img_');
        if ($tempPath === false) {
            imagedestroy($source);
            imagedestroy($target);
            return null;
        }

        $written = false;

        switch ($mime) {
            case 'image/png':
                $extension = 'png';
                $written = imagepng($target, $tempPath, $this->pngCompression);
                break;

            case 'image/gif':
                $extension = 'gif';
                $written = imagegif($target, $tempPath);
                break;

            case 'image/webp':
                $extension = 'webp';
                if (function_exists('imagewebp')) {
                    $written = imagewebp($target, $tempPath, $this->webpQuality);
                }
                break;

            default:
                $extension = 'jpg';
                $written = imagejpeg($target, $tempPath, $this->jpegQuality);
                break;
        }

        imagedestroy($source);
        imagedestroy($target);

        if (!$written) {
            @unlink($tempPath);
            return null;
        }

        return [
            'path' => $tempPath,
            'extension' => $extension,
        ];
    }
}
