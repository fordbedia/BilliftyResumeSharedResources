<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume;

class EloquentResumeRepository extends EloquentBaseRepository implements ResumeRepository
{
	public function all(): \Illuminate\Database\Eloquent\Collection
	{
		return $this->model->all()->loadMissing(Resume::relationships());
	}

	public function allLatest(): \Illuminate\Database\Eloquent\Collection
	{
		return $this->model->latest('updated_at', 'desc')->with(Resume::relationships())->get();
	}

	public function find(int $id): \Illuminate\Database\Eloquent\Model
	{
		return $this->model->find($id)->loadMissing(Resume::relationships());
	}

	public function getByKey(int $id): Resume
	{
		return $this->model->withoutGlobalScope('owned_by_user')
			->find($id)
			->loadMissing(Resume::relationships());
	}

	public function create(array $data): \Illuminate\Database\Eloquent\Model|array
	{
		return $this->model->create($data);
	}

	public function save(int $resumeId, array $data)
	{
		return $this->model->updateOrCreate(['id' => $resumeId], $data);
	}

	public function makeModel(): string
	{
		return Resume::class;
	}

	public function destroy(int $id)
	{
		return $this->model->destroy($id);
	}
}