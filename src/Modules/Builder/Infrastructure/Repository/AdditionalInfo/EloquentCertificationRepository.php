<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\AdditionalInfo;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\AdditionalInfo\CertificationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\AdditionalInfo\Certification;

class EloquentCertificationRepository extends EloquentBaseRepository implements CertificationRepository
{

	public function findBy(string $field, string $value)
	{
		return parent::findBy($field, $value);
	}

	public function create(array $data): \Illuminate\Database\Eloquent\Model|array
	{
		return $this->model->create($data);
	}

	public function makeModel(): string
	{
		return Certification::class;
	}
}