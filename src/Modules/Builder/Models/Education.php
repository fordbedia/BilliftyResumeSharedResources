<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Models;


use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $table = 'education';
	protected $guarded = [];
	protected $touches = ['resume'];

	public function resume()
	{
		return $this->belongsTo(Resume::class);
	}
}
