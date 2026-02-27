<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class EloquentResumeRepository extends EloquentBaseRepository implements ResumeRepository
{
	public function all(): Collection
	{
		return $this->model->all()->loadMissing(Resume::relationships());
	}

	public function allLatest(): Collection
	{
		return $this->model->latest('updated_at', 'desc')->with(Resume::relationships())->get();
	}

	public function paginated(?string $search = null, int $perPage = 10): LengthAwarePaginator
	{
		$query = $this->model
			->newQuery()
			->with(Resume::relationships())
			->latest('updated_at');

		if ($search) {
			$query->where(function ($builder) use ($search) {
				$builder
					->where('name', 'like', "%{$search}%")
					->orWhereHas('basic', function ($basicQuery) use ($search) {
						$basicQuery
							->where('name', 'like', "%{$search}%")
							->orWhere('label', 'like', "%{$search}%");
					});
			});
		}

		return $query->paginate($perPage);
	}

	public function find(int $id): ?Model
	{
		$resume = $this->model->find($id);

		return $resume?->loadMissing(Resume::relationships());
	}

	public function getByKey(int $id): Resume
	{
		return $this->model->withoutGlobalScope('owned_by_user')
			->find($id)
			->loadMissing(Resume::relationships());
	}

	public function create(array $data): Model|array
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
