<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Basic extends Model
{
    protected $table = 'basic';
	protected $guarded = [];
	protected $appends = ['image_url'];

	public function profile()
	{
		return $this->hasOne(Profile::class);
	}

	public function getImageUrlAttribute(): ?string
	{
		$path = $this->image; // "resume_images/2026/1/....jpg"
		if (!$path) return null;

		$disk = $this->image_disk ?? 'public'; // 'public' or 's3'

		return Storage::disk($disk)->url($path);
	}
}
