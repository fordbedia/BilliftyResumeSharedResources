<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ProfileRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Profile;

class EloquentProfileRepository extends EloquentBaseRepository implements ProfileRepository
{
	public function create(array $data): \Illuminate\Database\Eloquent\Model|array
	{
		return $this->model->create($data);
	}

	public function updateById(int $basicId, int $id, array $data)
	{
		$profile = $this->model->where('basic_id', $basicId)->where('id', $id)->firstOrFail();
		$profile->fill($data)->save();
		return $profile;
	}

	public function deleteMissing(int $resumeId, array $keepIds): void
	{
		$q = $this->model->where('basic_id', $resumeId);

		if (!empty($keepIds)) {
			$q->whereNotIn('id', $keepIds)->delete();
			return;
		}

		$q->delete();
	}

	public function makeModel(): string
	{
		return Profile::class;
	}
}