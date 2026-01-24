<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\EducationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Education;

class EloquentEducationRepository extends EloquentBaseRepository implements EducationRepository
{
	public function create(array $data): \Illuminate\Database\Eloquent\Model|array
	{
		return $this->model->create($data);
	}

	public function updateById(int $resumeId, int $id, array $data)
	{
		$work = $this->model->where('resume_id', $resumeId)->where('id', $id)->firstOrFail();
		$work->fill($data)->save();
		return $work;
	}

	public function deleteMissing(int $resumeId, array $keepIds): void
	{
		$q = $this->model->where('resume_id', $resumeId);

		if (!empty($keepIds)) {
			$q->whereNotIn('id', $keepIds)->delete();
			return;
		}

		// If client sent no work items, delete all (true sync behavior)
		$q->delete();
	}

	public function makeModel(): string
	{
		return Education::class;
	}
}