<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EducationJsonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'institution' => (string) ($this->resource->institution ?? ''),
            'area' => (string) ($this->resource->area ?? ''),
            'studyType' => (string) ($this->resource->studyType ?? ''),
            'startDate' => (string) ($this->resource->startDate ?? ''),
            'endDate' => $this->resource->endDate ?: null,
            'score' => $this->resource->score,
        ];
    }
}
