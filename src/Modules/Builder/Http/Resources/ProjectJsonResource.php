<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectJsonResource extends JsonResource
{
	public function toArray($request): array
	{
		return [
			'id' => $this->id ?? null,
			'resume_id' => $this->resume_id ?? null,
			'body' => $this->body ?? null,
			'is_active' => $this->is_active ?? null,
		];
	}
}