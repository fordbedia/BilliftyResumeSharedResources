<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ResumeRepository
{
	public function all(): Collection;

	public function allLatest(): Collection;

	public function paginated(?string $search = null, int $perPage = 10): LengthAwarePaginator;

	public function find(int $id): ?Model;

	public function getByKey(int $id): Resume;

	public function create(array $data): Model|array;

	public function save(int $resumeId, array $data);

	public function destroy(int $id);
}
