<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Storage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageProcessor
{

	public function __construct(protected UploadedFile $file, protected string $name)
	{
	}

	public static function make(UploadedFile $file, string $name)
	{
		return new static($file, $name);
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
			"resume_images/{$year}/{$month}",
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