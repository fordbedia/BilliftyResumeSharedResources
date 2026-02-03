<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Storage;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Storage\ImageProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageFileUploadProcessor implements ImageProcessor
{
	public function __construct(
		protected UploadedFile $file,
		protected string $name,
		protected string $storageDirectory = 'resume_images'
	) {}

	public static function make(UploadedFile $file, string $name, string $storageDirectory = 'resume_images')
	{
		return new static($file, $name, $storageDirectory);
	}

	public function store()
	{
		$base = Str::slug($this->name) ?: 'resume';

		$year  = now()->format('Y');
		$month = now()->format('n'); // 1-12
		$ext  = strtolower($this->file->getClientOriginalExtension() ?: $this->file->extension() ?: 'jpg');
		$hash = Str::lower(Str::random(8));
		$time = time();
		// /year/month/name.<short-hash>.<ext>
		$relativePath = "{$year}/{$month}/{$base}.{$hash}.{$ext}";

		// choose your folder + disk
		return $this->file->storeAs(
			"{$this->storageDirectory}/{$year}/{$month}",
			"{$base}.{$time}.{$hash}.{$ext}",
			'public' // or whatever disk you want
		);
	}

	public function deleteLastFile(string $column, Model $model)
	{
		$image = $model->{$column};
		if ($image) {
			unlink(storage_path("app/public/{$image}"));
		}
	}
}