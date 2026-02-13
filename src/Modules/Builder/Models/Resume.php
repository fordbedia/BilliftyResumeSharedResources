<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Models;


use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\US\Volunteering;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\AdditionalInfo\Certification;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\AdditionalInfo\Accomplishment;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\AdditionalInfo\Languages;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\US\Affiliation;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\US\Interest;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\US\Websites;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\US\Project;

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
		return $this->belongsTo(Templates::class);
	}

	public function colorScheme()
	{
		return $this->belongsTo(ColorScheme::class);
	}

	public function certificate()
	{
		return $this->hasOne(Certification::class, 'resume_id', 'id');
	}

	public function accomplishment()
	{
		return $this->hasOne(Accomplishment::class, 'resume_id', 'id');
	}

	public function languages()
	{
		return $this->hasOne(Languages::class);
	}

	public function affiliation()
	{
		return $this->hasOne(Affiliation::class);
	}

	public function interest()
	{
		return $this->hasOne(Interest::class);
	}

	public function volunteer()
	{
		return $this->hasOne(Volunteering::class);
	}

	public function websites()
	{
		return $this->hasOne(Websites::class);
	}

	public function project()
	{
		return $this->hasOne(Project::class);
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
			'template',
			'colorScheme',
			'certificate',
			'accomplishment',
			'languages.language',
			'affiliation',
			'interest',
			'volunteer',
			'websites.website',
			'project',
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
