<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Models\AdditionalInfo;


use Illuminate\Database\Eloquent\Model;

class Languages extends Model
{
    protected $table = 'languages';
	protected $guarded = [];

	public function language()
	{
		return $this->hasMany(Language::class);
	}
}
