<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WebsitesJsonResource extends JsonResource
{
	public function toArray($request): array
	{
		$resource = $this->resource;
		$websites = collect($resource?->website ?? [])
			->map(fn ($r) => [
				'websites_id' => $r?->websites_id,
				'url' => $r?->url,
			])
			->values()
			->all();

		return [
			'id' => $resource?->id,
			'resume_id' => $resource?->resume_id,
			'is_active' => $resource?->is_active,
			'websites' => $websites,
		];
	}
}
