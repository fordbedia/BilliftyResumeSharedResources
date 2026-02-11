<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\V2;

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
}