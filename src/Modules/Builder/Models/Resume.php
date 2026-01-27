<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Resume extends Model
{
    protected $table = 'resume';
	protected $guarded = [];

	public function basic()
	{
		return $this->hasOne(Basic::class);
	}

	public function education()
	{
		return $this->hasMany(Education::class);
	}

	public function work()
	{
		return $this->hasMany(Work::class);
	}

	public function skills()
	{
		return $this->hasMany(Skills::class);
	}

	public function reference()
	{
		return $this->hasMany(Reference::class);
	}

	public function template()
	{
		return $this->belongsToMany(
			Templates::class,
			'resume_template',
			'resume_id',
			'template_id',
			'id',
			'id'
		);
	}

	public static function relationships()
	{
		return [
			'basic',
			'basic.profile',
			'education' => fn ($q) => $q->orderBy('sort_order'),
			'work' => fn ($q) => $q->orderBy('sort_order'),
			'skills' => fn ($q) => $q->orderBy('sort_order'),
			'reference' => fn ($q) => $q->orderBy('sort_order'),
			'template'
		];
	}

	protected static function booted(): void
    {
        static::addGlobalScope('owned_by_user', function (Builder $builder) {
            // Only apply when authenticated; avoid breaking CLI/seeding/jobs
            $userId = Auth::id();
            if ($userId) {
                $builder->where('user_id', $userId);
            }
        });

        // Optional: auto-fill user_id on create
        static::creating(function (self $resume) {
            if (!$resume->user_id && Auth::id()) {
                $resume->user_id = Auth::id();
            }
        });
    }

    // Optional escape hatch for admin/internal jobs:
    public function scopeAllUsers(Builder $query): Builder
    {
        return $query->withoutGlobalScope('owned_by_user');
    }
}
