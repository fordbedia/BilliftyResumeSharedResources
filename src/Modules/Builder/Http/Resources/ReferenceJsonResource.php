<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferenceJsonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => (string) ($this->resource->name ?? ''),
            'reference' => (string) ($this->resource->reference ?? ''),
        ];
    }
}
