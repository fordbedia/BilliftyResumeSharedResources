<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkJsonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => (string) ($this->resource->name ?? ''),
            'position' => (string) ($this->resource->position ?? ''),
            'startDate' => (string) ($this->resource->startDate ?? ''),
            'endDate' => $this->resource->endDate ?: null,
            'summary' => (string) ($this->resource->summary ?? ''),
            'highlights' => $this->resource->highlights ? (array) $this->resource->highlights : [],
        ];
    }
}
