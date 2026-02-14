<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LanguagesJsonResource extends JsonResource
{
	public function toArray($request): array
	{
		return [
			'resume_id' => $this->resume_id ?? null,
			'is_active' => $this->is_active ?? null,
			'id' => $this->id ?? null,
			'languages' => $this->language?->map(fn ($r) => [
                'languages_id' => $r->languages_id,
                'language' => $r->language,
            ])->values()->all() ?? [],
		];
	}
}