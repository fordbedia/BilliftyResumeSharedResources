<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Models;


use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    protected $table = 'resume';
	protected $guarded = [];

	public function x()
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
}
