<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Repository;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\Eloquent\Repository\UserDataExportRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\UserEloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Models\UserDataExport;

class EloquentUserDataExportRepository extends UserEloquentBaseRepository implements UserDataExportRepository
{

	public function find(int $id): ?\Illuminate\Database\Eloquent\Model
	{
		return $this->model->find($id);
	}

	public function recent(int $userId)
	{
		return $this->model->where('user_id', $userId)
            ->whereIn('status', ['queued', 'processing', 'ready'])
            ->where('created_at', '>=', now()->subMinutes(5))
            ->latest()
            ->first();
	}

	public function findLatest(int $userId)
	{
		return $this->model->where('user_id', $userId)
            ->latest()
            ->first();
	}

	public function create(array $data): \Illuminate\Database\Eloquent\Model|array
	{
		return $this->model->create($data);
	}

	public function makeModel(): string
	{
		return UserDataExport::class;
	}
}