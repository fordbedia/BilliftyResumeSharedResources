<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeJsonResource extends JsonResource
{
   public function toArray($request): array
    {
        $resume = $this->resource;

        return [
            'basics' => [
                'name' => data_get($resume, 'basic.name'),
                'label' => data_get($resume, 'basic.label'),
                'email' => data_get($resume, 'basic.email'),
                'phone' => data_get($resume, 'basic.phone'),
                'url' => data_get($resume, 'basic.url'),
                'summary' => data_get($resume, 'basic.summary'),
                'location' => [
                    'address' => data_get($resume, 'basic.address'),
                    'postalCode' => data_get($resume, 'basic.postalCode'),
                    'city' => data_get($resume, 'basic.city'),
                    'region' => data_get($resume, 'basic.region'),
                    'countryCode' => data_get($resume, 'basic.countryCode'),
                ],
                'profiles' => array_values(array_filter([
                    // If you later have multiple profiles, map them here
                    // For now you only showed `basic.profile` (singular)
                    data_get($resume, 'basic.profile') ? [
                        'network' => data_get($resume, 'basic.profile.network'),
                        'username' => data_get($resume, 'basic.profile.username'),
                        'url' => data_get($resume, 'basic.profile.url'),
                    ] : null
                ])),
            ],

            'work' => $resume->work?->map(fn ($w) => [
                'name' => $w->name,
                'position' => $w->position,
                'startDate' => $w->startDate,
                'endDate' => $w->endDate,
                'summary' => $w->summary,
                'highlights' => $w->highlights ? (array) $w->highlights : [],
            ])->values()->all() ?? [],

            'education' => $resume->education?->map(fn ($e) => [
                'institution' => $e->institution,
                'area' => $e->area,
                'studyType' => $e->studyType,
                'startDate' => $e->startDate,
                'endDate' => $e->endDate,
                'score' => $e->score,
            ])->values()->all() ?? [],

            'skills' => $resume->skills?->map(fn ($s) => [
                'name' => $s->name,
                'level' => $s->level,
            ])->values()->all() ?? [],

            // IMPORTANT: your relation is `reference` (singular)
            'references' => $resume->reference?->map(fn ($r) => [
                'name' => $r->name,
                'reference' => $r->reference,
            ])->values()->all() ?? [],

            'template' => [
                'path' => data_get($resume, 'template.0.path'), // because template is a Collection
            ],
        ];
    }
}
