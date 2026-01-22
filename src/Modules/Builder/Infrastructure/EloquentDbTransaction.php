<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Application\Eloquent\Transactional;
use Illuminate\Support\Facades\DB;

class EloquentDbTransaction implements Transactional
{
	public function run(callable $fn)
	{
		return DB::transaction($fn);
	}
}