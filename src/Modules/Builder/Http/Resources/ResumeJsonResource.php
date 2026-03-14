<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Http\Resources;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume\ResumeStrengthService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeJsonResource extends JsonResource
{
   public function toArray($request): array
    {
        $resume = $this->resource;
		$sectionOrder = $this->resolveSectionOrder($resume);

        $data = [
			'name' => $resume->name,
            'basics' => [
                'name' => data_get($resume, 'basic.name'),
                'label' => data_get($resume, 'basic.label'),
                'email' => data_get($resume, 'basic.email'),
                'phone' => data_get($resume, 'basic.phone'),
                'url' => data_get($resume, 'basic.url'),
				'imageUrl' => data_get($resume, 'basic.image_url'),
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

            'skills' => $resume->skills,

            // IMPORTANT: your relation is `reference` (singular)
            'references' => $resume->reference?->map(fn ($r) => [
                'name' => $r->name,
                'reference' => $r->reference,
            ])->values()->all() ?? [],
			'certificate' => CertificationJsonResource::make($resume->certificate)->toArray($request),
			'accomplishment' => AccomplishmentJsonResource::make($resume->accomplishment)->toArray($request),
			'languages' => LanguagesJsonResource::make($resume->languages)->toArray($request),
			'affiliation' => AffiliationJsonResource::make($resume->affiliation)->toArray($request),
			'interest' => InterestJsonResource::make($resume->interest)->toArray($request),
			'volunteer' => VolunteerJsonResource::make($resume->volunteer)->toArray($request),
			'websites' => WebsitesJsonResource::make($resume->websites)->toArray($request),
			'project' => ProjectJsonResource::make($resume->project)->toArray($request),
			'colorScheme' => data_get($resume, 'colorScheme.primary') ?? data_get($resume, 'color_scheme.primary'),
			'sectionOrder' => $sectionOrder,
			'sectionGroups' => [
				'additional_information' => [
					'label' => 'Additional Information',
					'sections' => ['certificate', 'accomplishment', 'languages'],
				],
				'for_us_candidates' => [
					'label' => 'For US Candidates',
					'sections' => ['affiliation', 'interest', 'volunteer', 'websites', 'project'],
				],
			],
        ];

		$data['resumeStrength'] = ResumeStrengthService::make()->forResume($resume, $data);

		return $data;
    }

	protected function resolveSectionOrder($resume): array
	{
		$defaults = \BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume::DEFAULT_SECTION_ORDER;
		$incoming = is_array($resume->section_order ?? null) ? $resume->section_order : [];
		$normalized = [];

		foreach ($incoming as $item) {
			if (!is_string($item)) {
				continue;
			}
			if (!in_array($item, $defaults, true) || in_array($item, $normalized, true)) {
				continue;
			}
			$normalized[] = $item;
		}

		foreach ($defaults as $key) {
			if (!in_array($key, $normalized, true)) {
				$normalized[] = $key;
			}
		}

		return $normalized;
	}
}
