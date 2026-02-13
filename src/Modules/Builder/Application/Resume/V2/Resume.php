<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\V2;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\AdditionalInfo\AccomplishmentRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\AdditionalInfo\CertificationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\AdditionalInfo\LanguageRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\BasicRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\EducationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ProfileRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ReferenceRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\SkillsRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\TemplatesRepository;
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
				$languages = $this->languages->create(array_merge($languagesData['languages'], ['resume_id' => $resumeId]));
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


			return $basics->refresh();
		});
	}
}
