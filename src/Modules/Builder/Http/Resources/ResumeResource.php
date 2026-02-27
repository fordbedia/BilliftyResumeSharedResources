<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\ResumeStrengthService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
		$strength = ResumeStrengthService::make()->forResume($this->resource);

        return [
			'id' => $this->id ?? '',
			'name' => $this->name ?? '',
			'title' => $this->basic?->label ?? '',
			'lastUpdated' => $this->updated_at->diffForHumans() ?? '',
			'basics' => new BasicsJsonResource($this->basic),
			'template' => TemplateJsonResource::make($this->template),
			'resumeStrength' => $strength,
		];
    }
}
