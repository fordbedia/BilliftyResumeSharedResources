<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume;

use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\Resume;
use BilliftyResumeSDK\SharedResources\Modules\Builder\Models\ResumeStrength;

class ResumeStrengthService
{
    public function __construct(private ResumeStrengthScorer $scorer)
    {
    }

    public static function make(): self
    {
        return app(self::class);
    }

    public function forResume(Resume $resume, ?array $resumeJson = null, bool $forceRecompute = false): array
    {
        $resume->loadMissing('resumeStrength');

        $cached = $resume->resumeStrength;
        if (!$forceRecompute && $cached && $this->isFresh($resume, $cached)) {
            return $this->transform($cached);
        }

        $payload = $resumeJson ?? $this->toResumeJsonShape($resume);
        $scored = $this->scorer->score($payload);

        $strength = ResumeStrength::query()->updateOrCreate(
            ['resume_id' => $resume->id],
            [
                'user_id' => $resume->user_id,
                'score' => $scored['score'],
                'grade' => $scored['grade'],
                'passed' => $scored['passed'],
                'feedback' => $scored['feedback'],
                'notes' => $scored['notes'],
                'scorer_version' => $scored['version'] ?? ResumeStrengthScorer::VERSION,
                'scored_at' => now(),
            ]
        );

        $resume->setRelation('resumeStrength', $strength);

        return $this->transform($strength);
    }

    private function isFresh(Resume $resume, ResumeStrength $strength): bool
    {
        if (!$strength->scored_at) {
            return false;
        }

        if (($strength->scorer_version ?? '') !== ResumeStrengthScorer::VERSION) {
            return false;
        }

        $spellingFields = data_get($strength->feedback, 'ats.checks.spelling.fields');
        if (!is_array($spellingFields)) {
            return false;
        }

        $spelling = data_get($strength->feedback, 'ats.checks.spelling');
        if (!is_array($spelling)) {
            return false;
        }

        $stats = (array) data_get($spelling, 'stats', []);
        $checkedWords = array_sum(array_map(
            fn ($field) => (int) data_get($field, 'checkedWords', 0),
            $spellingFields
        ));
        $misspelledWords = array_sum(array_map(
            fn ($field) => (int) data_get($field, 'misspelledWords', 0),
            $spellingFields
        ));

        if ((int) ($stats['checkedWords'] ?? -1) !== $checkedWords) {
            return false;
        }
        if ((int) ($stats['misspelledWords'] ?? -1) !== $misspelledWords) {
            return false;
        }

        $status = (string) data_get($spelling, 'status', '');
        $providerAvailable = data_get($spelling, 'provider.available');
        if ($misspelledWords > 0 && $status !== 'warn') {
            return false;
        }
        if ($misspelledWords === 0 && $status === 'warn' && $providerAvailable !== false) {
            return false;
        }

        $latestContentUpdatedAt = $this->latestContentUpdatedAt($resume);
        if (!$latestContentUpdatedAt) {
            return true;
        }

        return $strength->scored_at->greaterThan($latestContentUpdatedAt);
    }

    private function latestContentUpdatedAt(Resume $resume)
    {
        $resume->loadMissing([
            'basic',
            'basic.profile',
            'work',
            'education',
            'skills',
            'reference',
            'certificate',
            'accomplishment',
            'languages',
            'languages.language',
            'affiliation',
            'interest',
            'volunteer',
            'websites',
            'websites.website',
            'project',
        ]);

        $timestamps = [];
        if ($resume->updated_at) {
            $timestamps[] = $resume->updated_at;
        }

        $collect = function (mixed $value) use (&$timestamps): void {
            if (!$value) {
                return;
            }

            if ($value instanceof \Illuminate\Database\Eloquent\Model) {
                if ($value->updated_at) {
                    $timestamps[] = $value->updated_at;
                }
                return;
            }

            if ($value instanceof \Illuminate\Support\Collection || is_array($value)) {
                foreach ($value as $item) {
                    if ($item instanceof \Illuminate\Database\Eloquent\Model && $item->updated_at) {
                        $timestamps[] = $item->updated_at;
                    }
                }
            }
        };

        $collect($resume->basic);
        $collect($resume->basic?->profile);
        $collect($resume->work);
        $collect($resume->education);
        $collect($resume->skills);
        $collect($resume->reference);
        $collect($resume->certificate);
        $collect($resume->accomplishment);
        $collect($resume->languages);
        $collect($resume->languages?->language);
        $collect($resume->affiliation);
        $collect($resume->interest);
        $collect($resume->volunteer);
        $collect($resume->websites);
        $collect($resume->websites?->website);
        $collect($resume->project);

        if (empty($timestamps)) {
            return null;
        }

        return collect($timestamps)->max();
    }

    private function transform(ResumeStrength $strength): array
    {
        return [
            'score' => (int) ($strength->score ?? 0),
            'grade' => (string) ($strength->grade ?? 'Weak'),
            'passed' => (bool) $strength->passed,
            'status' => $strength->passed ? 'passed' : 'failed',
            'threshold' => $this->scorer->passingScore(),
            'version' => (string) ($strength->scorer_version ?? ResumeStrengthScorer::VERSION),
            'feedback' => is_array($strength->feedback) ? $strength->feedback : [],
            'notes' => is_array($strength->notes) ? $strength->notes : [],
            'scoredAt' => $strength->scored_at?->toIso8601String(),
        ];
    }

    private function toResumeJsonShape(Resume $resume): array
    {
        $resume->loadMissing(Resume::relationships());

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
                    data_get($resume, 'basic.profile') ? [
                        'network' => data_get($resume, 'basic.profile.network'),
                        'username' => data_get($resume, 'basic.profile.username'),
                        'url' => data_get($resume, 'basic.profile.url'),
                    ] : null,
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
            'skills' => [
                'body' => (string) (
                    data_get($resume, 'skills.0.body')
                    ?? (
                        $resume->skills?->pluck('name')
                            ->filter(fn ($name) => filled($name))
                            ->implode(', ')
                    )
                    ?? ''
                ),
            ],
            'references' => $resume->reference?->map(fn ($r) => [
                'name' => $r->name,
                'reference' => $r->reference,
            ])->values()->all() ?? [],
            'certificate' => [
                'body' => data_get($resume, 'certificate.body'),
            ],
            'accomplishment' => [
                'body' => data_get($resume, 'accomplishment.body'),
            ],
            'affiliation' => [
                'body' => data_get($resume, 'affiliation.body'),
            ],
            'interest' => [
                'body' => data_get($resume, 'interest.body'),
            ],
            'volunteer' => [
                'body' => data_get($resume, 'volunteer.body'),
            ],
            'project' => [
                'body' => data_get($resume, 'project.body'),
            ],
        ];
    }
}
