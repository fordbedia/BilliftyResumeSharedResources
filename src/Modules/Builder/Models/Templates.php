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
}
