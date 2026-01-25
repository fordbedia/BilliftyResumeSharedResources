<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Models;


use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    protected $table = 'work';
	protected $guarded = [];
	protected $touches = ['resume'];

	public function resume()
	{
		return $this->belongsTo(Resume::class);
	}
}
