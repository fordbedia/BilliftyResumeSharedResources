<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkillsJsonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => (string) ($this->resource->name ?? ''),
            'level' => (string) ($this->resource->level ?? ''),
        ];
    }
}
