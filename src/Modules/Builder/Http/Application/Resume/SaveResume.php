<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Application\Resume;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Application\Eloquent\Repository\BasicRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Application\Eloquent\Repository\EducationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Application\Eloquent\Repository\ProfileRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Application\Eloquent\Repository\ReferenceRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Application\Eloquent\Repository\ResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Application\Eloquent\Repository\SkillsRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Application\Eloquent\Repository\WorkRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Application\Eloquent\Transactional;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentBasicRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentEducationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentProfileRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentReferenceRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentSkillsRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Infrastructure\Repository\EloquentWorkRepository;

class SaveResume
{
	public function __construct(
		protected ResumeRepository $resume,
		protected BasicRepository $basics,
		protected WorkRepository $work,
		protected EducationRepository $education,
		protected SkillsRepository $skills,
		protected ProfileRepository $profile,
		protected ReferenceRepository $reference,
		protected Transactional $transaction
	)
	{}

	public static function make()
	{
		return app(self::class);
	}

	public function upsert(int $userId, array $payload, int $resumeId = null,)
	{
		// Save and Update logic
		dump($payload);
		[
			'basics' 		=> $basics,
			'work' 			=> $work,
			'education' 	=> $education,
			'skills' 		=> $skills,
			'references' 	=> $references,
		] = $payload;

		return $this->transaction->run(function () use ($userId, $resumeId, $basics, $work, $education, $skills, $references) {
			if ($resumeId){
				$resume = $this->resume->find($resumeId);
			} else {
				$resume = $this->resume->create(['user_id' => $userId]);
			}
			if ($basics) {
				$this->basics->save(['resume_id' => $resume->id],[
					'name'			=> $basics['name'],
					'label' 		=> $basics['label'],
					'email'			=> $basics['email'],
					'phone'			=> $basics['phone'],
					'url'			=> $basics['url'],
					'address'		=> $basics['location']['address'],
					'postalCode' 	=> $basics['location']['postalCode'],
					'city' 			=> $basics['location']['city'],
					'region'		=> $basics['location']['region'],
					'summary'		=> $basics['summary'],
				]);
			}

			if ($work) {

			}

			dd($work);
		});
	}

	protected function massagePayload(array $payload, int $resumeId = null): array
	{
		$search = [];
		if ($resumeId) {
			$search = ['resume_id' => $resumeId];
		}
		return [
			'search' => $search,
			'data' => $payload
		];
		return $payload;
	}
}