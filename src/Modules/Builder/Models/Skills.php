<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Models;


use Illuminate\Database\Eloquent\Model;

class Skills extends Model
{
    protected $table = 'skills';
	protected $guarded = [];
	protected $touches = ['resume'];

	public function resume()
	{
		return $this->belongsTo(Resume::class);
	}
}
