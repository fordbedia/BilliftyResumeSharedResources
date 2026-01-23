<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Providers;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\BasicRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\EducationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ProfileRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ReferenceRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\SkillsRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\TemplatesRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\WorkRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Transactional;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentDbTransaction;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentBasicRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentEducationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentProfileRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentReferenceRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentSkillsRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentTemplatesRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentWorkRepository;
use Illuminate\Support\ServiceProvider;

class EloquentResumeRepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ResumeRepository::class, EloquentResumeRepository::class);
		$this->app->bind(BasicRepository::class, EloquentBasicRepository::class);
		$this->app->bind(EducationRepository::class, EloquentEducationRepository::class);
		$this->app->bind(ProfileRepository::class, EloquentProfileRepository::class);
		$this->app->bind(ReferenceRepository::class, EloquentReferenceRepository::class);
		$this->app->bind(SkillsRepository::class, EloquentSkillsRepository::class);
		$this->app->bind(WorkRepository::class, EloquentWorkRepository::class);
		$this->app->bind(Transactional::class, EloquentDbTransaction::class);
		$this->app->bind(TemplatesRepository::class, EloquentTemplatesRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
