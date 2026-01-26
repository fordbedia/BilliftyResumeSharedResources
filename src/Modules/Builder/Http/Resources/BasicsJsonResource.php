<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasicsJsonResource extends JsonResource
{
    public function toArray($request): array
    {
        if (!$this->resource) {
            return [
                'name' => '',
                'label' => '',
                'email' => '',
                'phone' => '',
                'url' => '',
                'summary' => '',
                'location' => [
                    'address' => '',
                    'postalCode' => '',
                    'city' => '',
                    'region' => '',
                    'countryCode' => '',
                ],
                'profiles' => [],
            ];
        }

        $p = $this->resource->profile;

        return [
            'name' => (string) ($this->resource->name ?? ''),
            'label' => (string) ($this->resource->label ?? ''),
            'email' => (string) ($this->resource->email ?? ''),
            'phone' => (string) ($this->resource->phone ?? ''),
            'url' => (string) ($this->resource->url ?? ''),
            'summary' => (string) ($this->resource->summary ?? ''),

            'location' => [
                'address' => (string) ($this->resource->address ?? ''),
                'postalCode' => (string) ($this->resource->postalCode ?? ''),
                'city' => (string) ($this->resource->city ?? ''),
                'region' => (string) ($this->resource->region ?? ''),
                'countryCode' => (string) ($this->resource->countryCode ?? ''),
            ],

            'profiles' => $p ? [[
                'network' => (string) ($p->network ?? ''),
                'username' => (string) ($p->username ?? ''),
                'url' => (string) ($p->url ?? ''),
            ]] : [],
        ];
    }
}
