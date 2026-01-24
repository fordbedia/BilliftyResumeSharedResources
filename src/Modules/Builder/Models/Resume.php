<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Models;


use Illuminate\Database\Eloquent\Model;

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
		return ['basic', 'basic.profile', 'education', 'work', 'skills', 'reference', 'template'];
	}
}
