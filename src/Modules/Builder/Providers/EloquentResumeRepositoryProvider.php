<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Providers;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\AdditionalInfo\AccomplishmentRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\AdditionalInfo\CertificationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\AdditionalInfo\LanguageRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\BasicRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ColorSchemeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\EducationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ProfileRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ReferenceRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\SkillsRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\TemplatesRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\US\AffiliationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\US\InterestRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\US\ProjectRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\US\VolunteeringRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\US\WebsiteRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\WorkRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Transactional;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\EloquentDbTransaction;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\AdditionalInfo\EloquentAccomplishmentRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\AdditionalInfo\EloquentCertificationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\AdditionalInfo\EloquentLanguageRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentBasicRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentColorSchemeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentEducationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentProfileRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentReferenceRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentSkillsRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentTemplatesRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentWorkRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\US\EloquentAffiliationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\US\EloquentInterestRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\US\EloquentProjectRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\US\EloquentVolunteeringRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\US\EloquentWebsiteRepository;
use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\AuthTokenIssuer;
use BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Auth\LaravelPassportTokenIssuer;
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
		$this->app->bind(ColorSchemeRepository::class, EloquentColorSchemeRepository::class);
		// ----------------------------------------------------------------------------
		// Additional Info
		// ----------------------------------------------------------------------------
		$this->app->bind(CertificationRepository::class, EloquentCertificationRepository::class);
		$this->app->bind(AccomplishmentRepository::class, EloquentAccomplishmentRepository::class);
		$this->app->bind(LanguageRepository::class, EloquentLanguageRepository::class);
		// ----------------------------------------------------------------------------
		// US Candidate
		// ----------------------------------------------------------------------------
		$this->app->bind(AffiliationRepository::class, EloquentAffiliationRepository::class);
		$this->app->bind(InterestRepository::class, EloquentInterestRepository::class);
		$this->app->bind(VolunteeringRepository::class, EloquentVolunteeringRepository::class);
		$this->app->bind(WebsiteRepository::class, EloquentWebsiteRepository::class);
		$this->app->bind(ProjectRepository::class, EloquentProjectRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
