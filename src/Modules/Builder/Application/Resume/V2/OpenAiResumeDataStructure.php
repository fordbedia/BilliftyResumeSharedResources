<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\V2;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\V2\Builder\ResumeBuilderAiResponseDataStructure;

class OpenAiResumeDataStructure implements ResumeBuilderAiResponseDataStructure
{
	private const UPSERT_FIELDS = ['basics', 'work', 'education', 'skills', 'references'];

	protected array $aiResume = [];

	public function __construct(protected Resume $resume)
	{
	}

	public static function make(): self
	{
		return app(self::class);
	}

	public function adapt(array $payload): array
	{
		$structured = (array) ($payload['structured_resume'] ?? $payload);
		$basics = (array) ($structured['basics'] ?? []);
		$location = (array) ($basics['location'] ?? []);

		$this->aiResume = [
			'basics' => [
				'name' => $this->string($basics['name'] ?? null),
				'label' => $this->string($basics['label'] ?? null),
				'url' => $this->string($basics['url'] ?? ($basics['website'] ?? null)),
				'image' => $this->string($basics['image'] ?? null),
				'email' => $this->string($basics['email'] ?? null),
				'phone' => $this->string($basics['phone'] ?? null),
				'address' => $this->string($basics['address'] ?? ($location['address'] ?? null)),
				'postalCode' => $this->string($basics['postalCode'] ?? ($location['postalCode'] ?? null)),
				'countryCode' => $this->string($basics['countryCode'] ?? ($location['countryCode'] ?? null)),
				'city' => $this->string($basics['city'] ?? ($location['city'] ?? null)),
				'region' => $this->string($basics['region'] ?? ($location['region'] ?? null)),
				'summary' => $this->string($basics['summary'] ?? null),
				'certifications' => $this->toggleBody($basics['certifications'] ?? null),
				'accomplishments' => $this->toggleBody($basics['accomplishments'] ?? null),
				'languages' => $this->toggleList($basics['languages'] ?? null, 'language'),
				'websites' => $this->toggleList($basics['websites'] ?? ($basics['website'] ?? null), 'url'),
				'affiliations' => $this->toggleBody($basics['affiliations'] ?? ($basics['affiliation'] ?? null)),
				'interests' => $this->toggleBody($basics['interests'] ?? ($basics['interest'] ?? null)),
				'volunteering' => $this->toggleBody($basics['volunteering'] ?? null),
				'projects' => $this->toggleBody($basics['projects'] ?? null),
			],
			'work' => $this->adaptWork($structured['work'] ?? []),
			'education' => $this->adaptEducation($structured['education'] ?? []),
			'skills' => $this->adaptSkills($structured['skills'] ?? []),
			'references' => $this->adaptReferences($structured['references'] ?? []),
		];

		return $this->aiResume;
	}

	public function upsert(
		int $userId,
		array $payload,
		?int $resumeId = null,
		int $templateId = 1,
		int $colorSchemeId = 1
	)
	{
		$normalized = $this->adapt($payload);
		$name = $this->string(data_get($normalized, 'basics.name')) ?: 'Untitled Resume';

		$createdResume = $this->resume->upsert('create', $userId, [
			'create' => [
				'name' => $name,
				'template' => $templateId,
				'color_scheme_id' => $colorSchemeId,
			],
		], $resumeId);

		$resolvedResumeId = (int) $createdResume->id;

		foreach (self::UPSERT_FIELDS as $field) {
			if ($field === 'education' && empty($normalized['education'])) {
				continue;
			}

			$this->resume->upsert($field, $userId, $normalized, $resolvedResumeId);
		}

		return $this->resume->upsert('finalize', $userId, [
			'finalize' => [
				'name' => $name,
				'template' => $templateId,
				'color_scheme_id' => $colorSchemeId,
			],
		], $resolvedResumeId);
	}

	private function adaptWork(mixed $work): array
	{
		if (!is_array($work)) {
			return [];
		}

		$rows = [];
		foreach ($work as $item) {
			if (!is_array($item)) {
				continue;
			}

			$rows[] = [
				'name' => $this->string($item['name'] ?? null),
				'position' => $this->string($item['position'] ?? null),
				'startDate' => $this->string($item['startDate'] ?? null),
				'endDate' => $this->string($item['endDate'] ?? null),
				'summary' => $this->string($item['summary'] ?? null),
			];
		}

		return $rows;
	}

	private function adaptEducation(mixed $education): array
	{
		if (!is_array($education)) {
			return [];
		}

		$rows = [];
		foreach ($education as $item) {
			if (!is_array($item)) {
				continue;
			}

			$rows[] = [
				'institution' => $this->string($item['institution'] ?? null),
				'area' => $this->string($item['area'] ?? null),
				'studyType' => $this->string($item['studyType'] ?? null),
				'startDate' => $this->string($item['startDate'] ?? null),
				'endDate' => $this->string($item['endDate'] ?? null),
			];
		}

		return $rows;
	}

	private function adaptSkills(mixed $skills): array
	{
		if (!is_array($skills)) {
			return [];
		}

		$rows = [];
		foreach ($skills as $item) {
			if (!is_array($item)) {
				continue;
			}

			$rows[] = [
				'name' => $this->string($item['name'] ?? null),
				'level' => $this->string($item['level'] ?? null),
			];
		}

		return $rows;
	}

	private function adaptReferences(mixed $references): array
	{
		if (!is_array($references)) {
			return [];
		}

		$rows = [];
		foreach ($references as $item) {
			if (!is_array($item)) {
				continue;
			}

			$rows[] = [
				'name' => $this->string($item['name'] ?? null),
				'reference' => $this->string($item['reference'] ?? null),
			];
		}

		return $rows;
	}

	private function toggleBody(mixed $value): array
	{
		if (is_array($value)) {
			$body = $this->string($value['body'] ?? null);
			$isActive = array_key_exists('is_active', $value) ? (bool) $value['is_active'] : $body !== '';

			return [
				'is_active' => $isActive,
				'body' => $body,
			];
		}

		$body = $this->string($value);
		return [
			'is_active' => $body !== '',
			'body' => $body,
		];
	}

	private function toggleList(mixed $value, string $key): array
	{
		if (is_array($value) && array_key_exists($key, $value)) {
			$list = $this->stringList($value[$key]);
			$isActive = array_key_exists('is_active', $value) ? (bool) $value['is_active'] : !empty($list);

			return [
				'is_active' => $isActive,
				$key => $list,
			];
		}

		$list = $this->stringList($value);
		return [
			'is_active' => !empty($list),
			$key => $list,
		];
	}

	private function stringList(mixed $value): array
	{
		if (is_string($value)) {
			$value = preg_split('/[,\n;]+/', $value) ?: [];
		}

		if (!is_array($value)) {
			return [];
		}

		return collect($value)
			->map(fn($item) => $this->string($item))
			->filter(fn($item) => $item !== '')
			->unique()
			->values()
			->toArray();
	}

	private function string(mixed $value): string
	{
		if ($value === null) {
			return '';
		}

		if (is_bool($value)) {
			return $value ? '1' : '0';
		}

		return trim((string) $value);
	}
}
