<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\AdditionalInfo;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\AdditionalInfo\CertificationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentBaseRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Certification;

class EloquentCertificationRepository extends EloquentBaseRepository implements CertificationRepository
{

	public function makeModel(): string
	{
		return Certification::class;
	}
}