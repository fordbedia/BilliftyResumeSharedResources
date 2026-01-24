<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\BasicRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Basic;

class EloquentBasicRepository extends EloquentBaseRepository implements BasicRepository
{
	public function create(array $data): \Illuminate\Database\Eloquent\Model|array
	{
		return Basic::create($data);
	}

	public function updateById(int $resumeId, int $id, array $data)
	{
		$basic = Basic::where('resume_id', $resumeId)->where('id', $id)->firstOrFail();
		$basic->fill($data)->save();
		return $basic;
	}

	public function makeModel(): string
	{
		return Basic::class;
	}
}