<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WebsitesJsonResource extends JsonResource
{
	public function toArray($request): array
	{
		return [
			'id' => $this->id ?? null,
			'resume_id' => $this->resume_id ?? null,
			'is_active' => $this->is_active ?? null,
			'websites' => $this->website->map(fn ($r) => [
				'websites_id' => $r->websites_id,
				'url' => $r->url,
			])
		];
	}
}