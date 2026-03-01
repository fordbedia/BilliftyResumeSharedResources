<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkillsJsonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'body' => (string) ($this->resource->body ?? ''),
        ];
    }
}
