<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Models;


use Illuminate\Database\Eloquent\Model;

class Templates extends Model
{
    protected $table = 'templates';
	protected $guarded = [];

	protected $casts = [
		'colors' => 'array',
	];

	public function resume()
	{
		return $this->belongsToMany(
			Resume::class,
			'resume_template',
			'template_id',
			'resume_id',
			'id',
			'id'
		);
	}
}
