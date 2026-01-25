<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume;

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
		protected TemplatesRepository $template
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
			'template'		=> $template
		] = $payload;

		return $this->transaction->run(function () use ($userId, $resumeId, $basics, $work, $education, $skills, $references, $template) {
			if ($resumeId){
				$resume = $this->resume->find($resumeId);
			} else {
				$resume = $this->resume->create(['user_id' => $userId]);
			}

			if ($basics) {
				$data = [
					'resume_id'		=> $resume->id,
					'name'			=> $basics['name'],
					'label' 		=> $basics['label'],
					'email'			=> $basics['email'],
					'image'			=> $basics['image'],
					'phone'			=> $basics['phone'],
					'url'			=> $basics['url'],
					'address'		=> $basics['location']['address'],
					'postalCode' 	=> $basics['location']['postalCode'],
					'countryCode'	=> $basics['location']['countryCode'],
					'city' 			=> $basics['location']['city'],
					'region'		=> $basics['location']['region'],
					'summary'		=> $basics['summary'],
				];
				if (!empty($basics['id'])) {
					// Update
					$basic = $this->basics->updateById($resume->id, (int) $basics['id'], $data);
				} else {
					// Create
					$basic = $this->basics->create($data);
				}
				// Profile
				if (!empty($basics['profiles'])) {
					foreach ($basics['profiles'] as $profile) {
						$data = [
							'basic_id' => $basic->id,
							'url' => $profile['url'],
						];
						if (!empty($profile['id'])) {
							// Update
							$this->profile->updateById($basics['id'], (int) $profile['id'], $data);
						} else {
							$this->profile->create($data);
						}
					}
				}
			}

			if (!empty($work)) {
				$keepIds = [];

				foreach ($work as $item) {
					$data = [
						'resume_id'  => $resume->id,
						'name'       => $item['name'] ?? '',
						'position'   => $item['position'] ?? null,
						'startDate'  => $item['startDate'] ?? null,
						'endDate'    => $item['endDate'] ?? null,
						'summary'    => $item['summary'] ?? null,
					];

					if (!empty($item['id'])) {
						// UPDATE (stable identity)
						$saved = $this->work->updateById($resume->id, (int) $item['id'], $data);
					} else {
						// CREATE (duplicates allowed → never search by name)
						$saved = $this->work->create($data);
					}

					$keepIds[] = $saved->id;
				}

				// delete rows not present anymore
				$this->work->deleteMissing($resume->id, $keepIds);
			}

			if (!empty($education)) {
				foreach ($education as $item) {
					$data = [
						'resume_id' => $resume->id,
						'institution' => $item['institution'],
						'area' => $item['area'],
						'studyType' => $item['studyType'],
						'startDate' => $item['startDate'],
						'endDate' => $item['endDate'],
					];
					if (!empty($item['id'])) {
						$savedEducation = $this->education->updateById($resume->id, (int) $item['id'], $data);
					} else {
						$savedEducation = $this->education->create($data);
					}
					$keepEducationIds[] = $savedEducation->id;
				}
				$this->education->deleteMissing($resume->id, $keepEducationIds);
			}

			if (!empty($skills)) {
				foreach ($skills as $item) {
					$data = [
						'level' => $item['level'],
						'resume_id' => $resume->id,
						'name' => $item['name']
					];
					if (!empty($item['id'])) {
						$savedSkills = $this->skills->updateById($resume->id, (int) $item['id'], $data);
					} else {
						$savedSkills = $this->skills->create($data);
					}
					$keepSkillsIds[] = $savedSkills->id;
				}
				$this->skills->deleteMissing($resume->id, $keepSkillsIds);
			}

			if (!empty($references)) {
				foreach ($references as $item) {
					$data = [
						'reference' => $item['reference'],
						'resume_id' => $resume->id,
						'name' => $item['name']
					];
					if (!empty($item['id'])) {
						$savedReferences = $this->reference->updateById($resume->id, (int) $item['id'], $data);
					} else {
						$savedReferences = $this->reference->create($data);
					}
					$keepReferencesIds[] = $savedReferences->id;
				}
				$this->reference->deleteMissing($resume->id, $keepReferencesIds);
			}

			if ($template) {
				$this->template->save($resume, $template);
			}

			return $resume->load(ResumeModel::relationships())->refresh();
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