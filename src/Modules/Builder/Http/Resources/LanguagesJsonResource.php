<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LanguagesJsonResource extends JsonResource
{
	public function toArray($request): array
	{
		$resource = $this->resource;
		$languages = collect($resource?->language ?? [])
			->map(fn ($r) => [
				'languages_id' => $r?->languages_id,
				'language' => $r?->language,
			])
			->values()
			->all();

		return [
			'resume_id' => $resource?->resume_id,
			'is_active' => $resource?->is_active,
			'id' => $resource?->id,
			'languages' => $languages,
		];
	}
}
