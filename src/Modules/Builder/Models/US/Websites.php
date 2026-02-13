<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Models\US;


use Illuminate\Database\Eloquent\Model;

class Websites extends Model
{
    protected $table = 'websites';
	protected $guarded = [];

	public function website()
	{
		return $this->hasOne(Website::class);
	}
}
