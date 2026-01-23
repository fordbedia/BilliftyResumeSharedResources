<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\BasicRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\EducationRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ProfileRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ReferenceRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\ResumeRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\SkillsRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Repository\WorkRepository;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Eloquent\Transactional;

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
					'image'			=> $basics['image'],
					'phone'			=> $basics['phone'],
					'url'			=> $basics['url'],
					'address'		=> $basics['location']['address'],
					'postalCode' 	=> $basics['location']['postalCode'],
					'city' 			=> $basics['location']['city'],
					'region'		=> $basics['location']['region'],
					'summary'		=> $basics['summary'],
				]);
			}

			if (!empty($work)) {
				foreach ($work as $item) {
					$this->work->save(['resume_id' => $resume->id, 'name' => $item['name']],[
						'position' => $item['position'],
						'startDate' => $item['startDate'],
						'endDate' => $item['endDate'],
						'summary' => $item['summary'],
					]);
				}
			}

			if (!empty($education)) {
				foreach ($education as $item) {
					$this->education->save(
						['resume_id' => $resume->id, 'institution' => $item['institution']],
						[
							'area' => $item['area'],
							'studyType' => $item['studyType'],
							'startDate' => $item['startDate'],
							'endDate' => $item['endDate'],
						]);
				}
			}

			if (!empty($skills)) {
				foreach ($skills as $item) {
					$this->skills->save(
						['resume_id' => $resume->id, 'name' => $item['name']],
						['level' => $item['level']]
					);
				}
			}

			if (!empty($references)) {
				foreach ($references as $item) {
					$this->reference->save(
						['resume_id' => $resume->id, 'name' => $item['name']],
						['reference' => $item['reference']]
					);
				}
			}

			return $resume->refresh();
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