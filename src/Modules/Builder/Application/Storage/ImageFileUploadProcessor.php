<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Storage;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Storage\UploadedImageResizer;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Storage\ImageProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageFileUploadProcessor implements ImageProcessor
{
	public function __construct(
		protected UploadedFile $file,
		protected string $name,
		protected string $storageDirectory = 'resume_images',
		protected string $disk = 'public',
		protected ?UploadedImageResizer $resizer = null,
		protected ?int $maxWidth = null,
		protected ?int $maxHeight = null,
	) {
	}

	public static function make(
		UploadedFile $file,
		string $name,
		string $storageDirectory = 'resume_images',
		string $disk = 'public',
		?UploadedImageResizer $resizer = null,
		?int $maxWidth = null,
		?int $maxHeight = null,
	)
	{
		return new static($file, $name, $storageDirectory, $disk, $resizer, $maxWidth, $maxHeight);
	}

	public function store(?int $maxWidth = null, ?int $maxHeight = null)
	{
		$base = Str::slug($this->name) ?: 'resume';

		$year  = now()->format('Y');
		$month = now()->format('n'); // 1-12
		$hash = Str::lower(Str::random(8));
		$time = time();
		$directory = "{$this->storageDirectory}/{$year}/{$month}";
		$resizer = $this->resolveResizer($maxWidth, $maxHeight);
		$resized = $resizer?->resizeToTemp($this->file);

		if ($resized && isset($resized['path'], $resized['extension'])) {
			$ext = strtolower((string) $resized['extension']);
			$filename = "{$base}.{$time}.{$hash}.{$ext}";
			$path = "{$directory}/{$filename}";
			$stream = @fopen($resized['path'], 'rb');

			if ($stream !== false) {
				Storage::disk($this->disk)->put($path, $stream);
				fclose($stream);
				@unlink($resized['path']);
				return $path;
			}

			@unlink($resized['path']);
		}

		$ext = strtolower($this->file->getClientOriginalExtension() ?: $this->file->extension() ?: 'jpg');
		return $this->file->storeAs($directory, "{$base}.{$time}.{$hash}.{$ext}", $this->disk);
	}

	protected function resolveResizer(?int $maxWidth = null, ?int $maxHeight = null): ?UploadedImageResizer
	{
		// If a custom adapter is provided, use it as-is.
		if ($this->resizer) {
			return $this->resizer;
		}

		$resolvedWidth = $maxWidth
			?? $this->maxWidth
			?? (int) config('builder.image_upload.max_width', 1200);
		$resolvedHeight = $maxHeight
			?? $this->maxHeight
			?? (int) config('builder.image_upload.max_height', 1200);

		return new GdImageUploadResizer(
			max($resolvedWidth, 1),
			max($resolvedHeight, 1),
			(int) config('builder.image_upload.jpeg_quality', 85),
			(int) config('builder.image_upload.webp_quality', 85),
			(int) config('builder.image_upload.png_compression', 6),
		);
	}

	public function deleteLastFile(string $column, Model $model)
	{
		$image = $model->{$column};
		if ($image) {
			try {
				unlink(storage_path("app/public/{$image}"));
			} catch(\Throwable $e) {}
		}
	}

	public static function deleteFile(string $path, ?string $disk = 'public'): void
	{
		$disk = $disk ?? 'public';
		if (Storage::disk($disk)->exists($path)) {
			Storage::disk($disk)->delete($path);
		}
	}
}
