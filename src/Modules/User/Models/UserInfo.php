<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UserInfo extends Model
{
    protected $table = 'user_info';
	protected $guarded = [];

	protected $appends = ['avatar_url'];

	public function getAvatarUrlAttribute(): ?string
	{
		$path = $this->avatar; // "resume_images/2026/1/....jpg"
		if (!$path) return null;

		$disk = $this->avatar_disk ?? 'public'; // 'public' or 's3'

		return Storage::disk($disk)->url($path);
	}
}
