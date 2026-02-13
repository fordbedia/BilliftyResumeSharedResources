<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\V2;

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
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume as ResumeModel;
use Illuminate\Support\Arr;

class Resume
{
	public function __construct(
		protected ResumeRepository $resume,
		protected BasicRepository $basics,
		protected WorkRepository $work,
		protected EducationRepository $education,
		protected SkillsRepository $skills,
		protected ProfileRepository $profile,
		protected ReferenceRepository $reference,
		protected Transactional $transaction,
		protected TemplatesRepository $template,
		protected CertificationRepository $certifications,
		protected AccomplishmentRepository $accomplishments,
		protected LanguageRepository $languages,
		protected AffiliationRepository $affiliation,
		protected InterestRepository $interest,
		protected VolunteeringRepository $volunteering,
		protected WebsiteRepository $websites,
		protected ProjectRepository $projects
	)
	{}

	public static function make()
	{
		return app(self::class);
	}

	public function upsert(string $modelName, int $userId, array $payload, int $resumeId = null)
	{
		return $this->$modelName($userId, $payload, $resumeId);
	}

	public function create(int $userId, array $payload, int $resumeId = null)
	{
		['create' => $create,] = $payload;
		return $this->transaction->run(function () use ($create, $resumeId, $userId) {
			if ($resumeId){
				$resume = $this->resume->find($resumeId);
				$resume->forceFill([
					'name' => $create['name'],
					'template_id' => $create['template'],
					'color_scheme_id' => $create['color_scheme_id']
				])->save();
			} else {
				$resume = $this->resume->create([
					'user_id' => $userId,
					'name' => $create['name'],
					'template_id' => $create['template'],
					'color_scheme_id' => $create['color_scheme_id']
				]);
			}
			return $resume->load(ResumeModel::relationships())->refresh();
		});
	}

	protected function basics(int $userId, array $payload, int $resumeId = null)
	{
		['basics' => $basicsPayload] = $payload;
		return $this->transaction->run(function () use ($basicsPayload, $resumeId, $userId) {
			$data = Arr::only($basicsPayload, [
				'name',
				'label',
				'url',
				'image',
				'email',
				'phone',
				'address',
				'postalCode',
				'countryCode',
				'city',
				'region',
				'summary',
			]);

			$basics = $this->basics->findBy('resume_id', $resumeId);
			if ($basics) {
				$basics->forceFill($data)->save();
			} else {
				$basics = $this->basics->create(array_merge($data, ['resume_id' => $resumeId]));
			}
			// ----------------------------------------------------------------------------
			// Additional Information
			// ----------------------------------------------------------------------------
			// Certifications
			$certificateData = Arr::only($basicsPayload, ['certifications']);
			$certifications = $this->certifications->findBy('resume_id', $resumeId);
			if ($certifications) {
				$certifications->forceFill($basicsPayload['certifications'])->save();
			} else {
				$certifications = $this->certifications->create(array_merge($certificateData['certifications'], ['resume_id' => $resumeId]));
			}
			// Accomplishments
			$accomplishmentData = Arr::only($basicsPayload, ['accomplishments']);
			$accomplishments = $this->accomplishments->findBy('resume_id', $resumeId);
			if ($accomplishments) {
				$accomplishments->forceFill($accomplishmentData['accomplishments'])->save();
			} else {
				$accomplishments = $this->accomplishments->create(array_merge($accomplishmentData['accomplishments'], ['resume_id' => $resumeId]));
			}
			// Languages
			$languagesData = Arr::only($basicsPayload, ['languages']);
			$language = collect($languagesData['languages']['language'] ?? [])
				->filter(fn ($value) => filled($value))
				->unique()
				->values()
				->toArray();
			unset($languagesData['languages']['language']);

			$languages = $this->languages->findBy('resume_id', $resumeId);
			if ($languages) {
				$languages->forceFill($languagesData['languages'])->save();
			} else {
				$languages = $this->languages->save($resumeId, array_merge($languagesData['languages'], ['resume_id' => $resumeId]));
			}

			// Replace rows to avoid duplicates on subsequent updates.
			$languages->language()->delete();
			if (!empty($language)) {
				$languagesId = $languages->id;
				$languageRows = collect($language)
					->map(fn ($value) => [
						'language' => $value,
						'languages_id' => $languagesId,
					])
					->values()
					->toArray();
				$languages->language()->createMany($languageRows);
			}
			// ----------------------------------------------------------------------------
			// For US Candicates
			// ----------------------------------------------------------------------------
			// Affiliations
			$affiliationsData = Arr::only($basicsPayload, ['affiliations']);
			$affiliations = $this->affiliation->findBy('resume_id', $resumeId);
			if ($affiliations) {
				$affiliations->forceFill($affiliationsData['affiliations'])->save();
			} else {
				$this->affiliation->create(array_merge($affiliationsData['affiliations'], ['resume_id' => $resumeId]));
			}
			// Interest
			$interestData = Arr::only($basicsPayload, ['interests']);
			$interest = $this->interest->findBy('resume_id', $resumeId);
			if ($interest) {
				$interest->forceFill($interestData['interests'])->save();
			} else {
				$this->interest->create(array_merge($interestData['interests'], ['resume_id' => $resumeId]));
			}
 			// Volunteering
			$volunteeringData = Arr::only($basicsPayload, ['volunteering']);
			$volunteering = $this->volunteering->findBy('resume_id', $resumeId);
			if ($volunteering) {
				$volunteering->forceFill($volunteeringData['volunteering'])->save();
			} else {
				$this->volunteering->create(array_merge($volunteeringData['volunteering'], ['resume_id' => $resumeId]));
			}
			// Websites
			$websitesData = Arr::only($basicsPayload, ['websites']);
			$website = collect($websitesData['websites']['url'] ?? [])
				->filter(fn ($value) => filled($value))
				->unique()
				->values()
				->toArray();
			unset($websitesData['websites']['url']);

			$websites = $this->websites->findBy('resume_id', $resumeId);
			if ($websites) {
				$websites->forceFill($websitesData['websites'])->save();
			} else {
				$websites = $this->websites->save($resumeId, array_merge($websitesData['websites'], ['resume_id' => $resumeId]));
			}

			// Replace rows to avoid duplicates on subsequent updates.
			$websites->website()->delete();
			if (!empty($website)) {
				$websiteId = $websites->id;
				$websiteRows = collect($website)
					->map(fn ($value) => [
						'url' => $value,
						'websites_id' => $websiteId,
					])
					->values()
					->toArray();
				$websites->website()->createMany($websiteRows);
			}

			// Projects
			$projectsData = Arr::only($basicsPayload, ['projects']);
			$projects = $this->projects->findBy('resume_id', $resumeId);
			if ($projects) {
				$projects->forceFill($projectsData['projects'])->save();
			} else {
				$this->projects->create(array_merge($projectsData['projects'], ['resume_id' => $resumeId]));
			}

			return $basics->refresh();
		});
	}

	protected function work(int $userId, array $payload, int $resumeId = null)
	{
		['work' => $workPayload] = $payload;

		return $this->transaction->run(function () use ($workPayload, $resumeId, $userId) {
			$keepIds = [];
			foreach($workPayload as $i => $work){
				$data = [
					'resume_id'  	=> $resumeId,
					'name'       	=> $work['name'] ?? '',
					'position'   	=> $work['position'] ?? null,
					'startDate'  	=> $work['startDate'] ?? null,
					'endDate'    	=> $work['endDate'] ?? null,
					'summary'    	=> $work['summary'] ?? null,
					'sort_order' 	=> $i
				];
				if(!empty($work['id'])){
					$saved = $this->work->updateById($resumeId, (int) $work['id'], $data);
				} else {
					// CREATE (duplicates allowed → never search by name)
					$saved = $this->work->create($data);
				}

				$keepIds[] = $saved->id;
			}
			// delete rows not present anymore
			$this->work->deleteMissing($resumeId, $keepIds);

			$workModel = $this->work->findBy('resume_id', $resumeId);
			return $workModel->refresh();
		});
	}

	protected function education(int $userId, array $payload, int $resumeId = null)
	{
		['education' => $educationPayload] = $payload;
		return $this->transaction->run(function () use ($educationPayload, $resumeId, $userId) {
			$keepIds = [];
			foreach ($educationPayload as $i => $item) {
					$data = [
						'resume_id' 	=> $resumeId,
						'institution' 	=> $item['institution'],
						'area' 			=> $item['area'],
						'studyType' 	=> $item['studyType'],
						'startDate' 	=> $item['startDate'],
						'endDate' 		=> $item['endDate'],
						'sort_order' 	=> $i
					];
					if (!empty($item['id'])) {
						$savedEducation = $this->education->updateById($resumeId, (int) $item['id'], $data);
					} else {
						$savedEducation = $this->education->create($data);
					}
					$keepEducationIds[] = $savedEducation->id;
				}
				$this->education->deleteMissing($resumeId, $keepEducationIds);
		});
	}

	protected function skills(int $userId, array $payload, int $resumeId = null)
	{
		['skills' => $skillsPayload] = $payload;
		return $this->transaction->run(function () use ($skillsPayload, $resumeId, $userId) {
			$keepSkillsIds = [];
			foreach ($skillsPayload as $i => $item) {
					$data = [
						'level' 		=> $item['level'],
						'resume_id' 	=> $resumeId,
						'name' 			=> $item['name'],
						'sort_order' 	=> $i
					];
					if (!empty($item['id'])) {
						$savedSkills = $this->skills->updateById($resumeId, (int) $item['id'], $data);
					} else {
						$savedSkills = $this->skills->create($data);
					}
					$keepSkillsIds[] = $savedSkills->id;
				}
				$this->skills->deleteMissing($resumeId, $keepSkillsIds);
		});
	}

	protected function references(int $userId, array $payload, int $resumeId = null)
	{
		['references' => $referencesPayload] = $payload;
		return $this->transaction->run(function () use ($referencesPayload, $resumeId, $userId) {
			$keepReferencesIds = [];
			foreach ($referencesPayload as $i => $item) {
				$data = [
					'reference' 	=> $item['reference'],
					'resume_id' 	=> $resumeId,
					'name' 			=> $item['name'],
					'sort_order' 	=> $i
				];
				if (!empty($item['id'])) {
					$savedReferences = $this->reference->updateById($resumeId, (int) $item['id'], $data);
				} else {
					$savedReferences = $this->reference->create($data);
				}
				$keepReferencesIds[] = $savedReferences->id;
			}
			$this->reference->deleteMissing($resumeId, $keepReferencesIds);
		});
	}

	protected function finalize(int $userId, array $payload, int $resumeId = null)
	{
		['finalize' => $finalizePayload] = $payload;
		// Resume name
		$this->resume->save($resumeId, [
			'name' => $finalizePayload['name'],
			'template_id' => $finalizePayload['template'],
			'color_scheme_id' => $finalizePayload['color_scheme_id']
		]);

		return $this->resume->find($resumeId);
	}
}
