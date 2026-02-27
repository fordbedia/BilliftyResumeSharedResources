<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Application\Resume;

use Illuminate\Support\Facades\Http;

class ResumeStrengthScorer
{
    public const PASSING_SCORE = 75;
    public const VERSION = 'v1.9';

    private array $actionVerbs = [
        'achieved', 'analyzed', 'architected', 'automated', 'built', 'created', 'defined',
        'delivered', 'deployed', 'designed', 'developed', 'drove', 'executed', 'generated',
        'implemented', 'improved', 'increased', 'integrated', 'launched', 'led', 'managed',
        'migrated', 'optimized', 'orchestrated', 'reduced', 'refactored', 'resolved', 'scaled',
        'streamlined'
    ];

    private array $weakPhrases = [
        'responsible for', 'worked on', 'helped', 'assisted', 'participated in', 'tasked with',
    ];

    private array $stopWords = [
        'the', 'and', 'with', 'from', 'that', 'this', 'were', 'have', 'into', 'over', 'across',
        'team', 'using', 'used', 'for', 'your', 'their', 'while', 'where', 'when', 'which',
    ];

    private array $spellIgnoreWords = [
        'api', 'apis', 'aws', 'azure', 'backend', 'bitbucket', 'ci', 'cd', 'css', 'devops',
        'docker', 'figma', 'firebase', 'frontend', 'github', 'gitlab', 'graphql', 'html',
        'ios', 'javascript', 'jira', 'json', 'jwt', 'k8s', 'kubernetes', 'laravel', 'linux',
        'macos', 'microservice', 'microservices', 'mongodb', 'mysql', 'nextjs', 'nginx', 'node',
        'oauth', 'okta', 'php', 'postgres', 'postgresql', 'react', 'redis', 'rest', 'restful',
        'saas', 'scrum', 'seo', 'sso', 'tailwind', 'terraform', 'typescript', 'ui', 'ux', 'vue',
		'sql',
    ];

    public function passingScore(): int
    {
        return self::PASSING_SCORE;
    }

    public function score(array $resume): array
    {
        $score = 0;
        $notes = [];

        [$basicsPoints, $basicsFeedback, $basicsNotes] = $this->scoreBasics((array) ($resume['basics'] ?? []));
        [$workPoints, $workFeedback, $workNotes] = $this->scoreWork((array) ($resume['work'] ?? []));
        [$educationPoints, $educationFeedback, $educationNotes] = $this->scoreEducation((array) ($resume['education'] ?? []));
        [$skillsPoints, $skillsFeedback, $skillsNotes] = $this->scoreSkills((array) ($resume['skills'] ?? []));
        [$atsPoints, $atsFeedback, $atsNotes] = $this->scoreAts((array) $resume);

        $score += $basicsPoints + $workPoints + $educationPoints + $skillsPoints + $atsPoints;
        $score = max(0, min(100, (int) round($score)));

        $notes = array_merge($notes, $basicsNotes, $workNotes, $educationNotes, $skillsNotes, $atsNotes);
        $notes = array_values(array_unique(array_filter(array_map('trim', $notes))));

        return [
            'score' => $score,
            'grade' => $this->grade($score),
            'passed' => $score >= self::PASSING_SCORE,
            'status' => $score >= self::PASSING_SCORE ? 'passed' : 'failed',
            'threshold' => self::PASSING_SCORE,
            'version' => self::VERSION,
            'feedback' => [
                'basics' => $basicsFeedback,
                'work' => $workFeedback,
                'education' => $educationFeedback,
                'skills' => $skillsFeedback,
                'ats' => $atsFeedback,
            ],
            'notes' => $notes,
        ];
    }

    private function scoreBasics(array $basics): array
    {
        $points = 0;
        $notes = [];
        $checks = [];

        $name = $this->value($basics['name'] ?? null);
        if ($name !== '') {
            $points += 4;
            $checks['name'] = $this->ok('Name is present.');
        } else {
            $checks['name'] = $this->missing('Add your full name in Basics.');
            $notes[] = 'Add your full name in the Basics section.';
        }

        $label = $this->value($basics['label'] ?? null);
        if ($label !== '') {
            $points += 3;
            $checks['label'] = $this->ok('Professional title is present.');
        } else {
            $checks['label'] = $this->warn('Add a clear target title (for example: Senior Frontend Engineer).');
            $notes[] = 'Add a clear job title in Basics (label).';
        }

        $summary = $this->value($basics['summary'] ?? null);
        $summaryLength = mb_strlen($summary);
        if ($summaryLength >= 60 && $summaryLength <= 700) {
            $points += 8;
            $checks['summary'] = $this->ok('Summary length is ATS-friendly.');
        } elseif ($summaryLength >= 25) {
            $points += 5;
            $checks['summary'] = $this->warn('Summary exists but can be stronger with quantified impact and keywords.');
            $notes[] = 'Strengthen summary with measurable impact and relevant keywords.';
        } else {
            $checks['summary'] = $this->missing('Add a 2-4 line professional summary with measurable impact.');
            $notes[] = 'Add a professional summary (2-4 lines) focused on outcomes.';
        }

        $email = $this->value($basics['email'] ?? null);
        $phone = $this->value($basics['phone'] ?? null);
        $url = $this->value($basics['url'] ?? null);

        $contactScore = 0;
        if ($email !== '') {
            $contactScore += 2;
        }
        if ($phone !== '') {
            $contactScore += 2;
        }
        if ($url !== '') {
            $contactScore += 2;
        }
        $points += $contactScore;

        if ($contactScore >= 4) {
            $checks['contact'] = $this->ok('Contact details are complete.');
        } elseif ($contactScore > 0) {
            $checks['contact'] = $this->warn('Add at least email plus phone or portfolio URL.');
            $notes[] = 'Include complete contact details: email and phone or portfolio URL.';
        } else {
            $checks['contact'] = $this->missing('Add contact details so recruiters can reach you quickly.');
            $notes[] = 'Add contact details in Basics (email, phone, URL).';
        }

        $location = (array) ($basics['location'] ?? []);
        $city = $this->value($location['city'] ?? null);
        $region = $this->value($location['region'] ?? null);
        if ($city !== '' || $region !== '') {
            $points += 2;
            $checks['location'] = $this->ok('Location is present.');
        } else {
            $checks['location'] = $this->warn('Add at least city and state/region.');
        }

        $profiles = (array) ($basics['profiles'] ?? []);
        if (count($profiles) > 0) {
            $points += 1;
            $checks['profiles'] = $this->ok('Professional profile links are present.');
        } else {
            $checks['profiles'] = $this->warn('Add LinkedIn or portfolio profile URL.');
        }

        $max = 25;
        $points = min($max, $points);

        return [$points, $this->sectionFeedback($points, $max, $checks), $notes];
    }

    private function scoreWork(array $work): array
    {
        $points = 0;
        $notes = [];
        $checks = [];

        if (count($work) === 0) {
            $checks['presence'] = $this->missing('Add at least one work experience entry.');
            return [0, $this->sectionFeedback(0, 40, $checks), ['Add at least one work experience entry.']];
        }

        $points += 8;
        $checks['presence'] = $this->ok('Work history is present.');

        $completeRoles = 0;
        $allBullets = [];
        foreach ($work as $job) {
            $name = $this->value($job['name'] ?? null);
            $position = $this->value($job['position'] ?? null);
            $startDate = $this->value($job['startDate'] ?? null);
            if ($name !== '' && $position !== '' && $startDate !== '') {
                $completeRoles++;
            }
            $allBullets = array_merge($allBullets, $this->extractBullets((array) $job));
        }

        $completenessRatio = count($work) > 0 ? $completeRoles / count($work) : 0;
        $points += (int) round(6 * min(1, $completenessRatio));
        if ($completenessRatio >= 0.75) {
            $checks['roleCompleteness'] = $this->ok('Roles include employer, title, and start date.');
        } else {
            $checks['roleCompleteness'] = $this->warn('Each role should include employer, title, and dates.');
            $notes[] = 'Ensure every work entry has company, title, start date, and end date/current.';
        }

        $totalBullets = count($allBullets);
        if ($totalBullets === 0) {
            $checks['bulletDensity'] = $this->missing('Add bullet points or line-broken achievements for each role.');
            $notes[] = 'Add bullet-style achievements under each role.';
            return [min(40, $points), $this->sectionFeedback(min(40, $points), 40, $checks), $notes];
        }

        $idealRoles = 0;
        foreach ($work as $job) {
            $bulletCount = count($this->extractBullets((array) $job));
            if ($bulletCount >= 3 && $bulletCount <= 6) {
                $idealRoles++;
            }
        }
        $densityRatio = count($work) > 0 ? $idealRoles / count($work) : 0;
        $points += (int) round(4 * min(1, $densityRatio));
        if ($densityRatio >= 0.5) {
            $checks['bulletDensity'] = $this->ok('Most roles use 3-6 bullets.');
        } else {
            $checks['bulletDensity'] = $this->warn('Aim for 3-6 concise bullets per role.');
            $notes[] = 'Aim for 3-6 concise bullets per role for better ATS parsing.';
        }

        $actionVerbBullets = 0;
        $metricBullets = 0;
        $weakPhraseHits = 0;

        foreach ($allBullets as $bullet) {
            $text = mb_strtolower(trim($bullet));
            if ($this->startsWithActionVerb($text)) {
                $actionVerbBullets++;
            }
            if ($this->hasMetric($text)) {
                $metricBullets++;
            }
            if ($this->containsWeakPhrase($text)) {
                $weakPhraseHits++;
            }
        }

        $actionRatio = $actionVerbBullets / $totalBullets;
        $points += (int) round(8 * min(1, $actionRatio));
        if ($actionRatio >= 0.45) {
            $checks['actionVerbs'] = $this->ok('Action verbs are used effectively.');
        } else {
            $checks['actionVerbs'] = $this->warn('Start more bullets with strong action verbs.');
            $notes[] = 'Start more bullets with action verbs (Built, Led, Improved, Reduced).';
        }

        $metricRatio = $metricBullets / $totalBullets;
        $points += (int) round(10 * min(1, $metricRatio));
        $metricTargetRatio = $totalBullets >= 8 ? 0.15 : 0.08;
        $hasReasonableMetricCoverage = $metricRatio >= $metricTargetRatio || ($metricBullets >= 1 && $totalBullets <= 6);
        if ($hasReasonableMetricCoverage) {
            $checks['metrics'] = $this->ok('Impact metrics are present.');
        } else {
            $checks['metrics'] = $this->warn('Add numbers (%, $, time, scale, users) to show measurable impact.');
            $notes[] = 'Add measurable impact to more bullets (percentages, revenue, time saved, scale).';
        }

        $weakPhraseScore = max(0, 4 - min(2, $weakPhraseHits));
        $points += $weakPhraseScore;
        if ($weakPhraseHits > 0) {
            $checks['phrasing'] = $this->warn('Replace weak phrasing with outcome-focused statements.');
            $notes[] = 'Replace phrases like "responsible for" with outcome-focused language.';
        } else {
            $checks['phrasing'] = $this->ok('Bullet phrasing is outcome-focused.');
        }

        $max = 40;
        $points = min($max, $points);

        return [$points, $this->sectionFeedback($points, $max, $checks), $notes];
    }

    private function scoreEducation(array $education): array
    {
        $points = 0;
        $notes = [];
        $checks = [];

        if (count($education) === 0) {
            $checks['presence'] = $this->missing('Add at least one education entry.');
            $notes[] = 'Add education details even if concise.';
            return [0, $this->sectionFeedback(0, 10, $checks), $notes];
        }

        $points += 4;
        $checks['presence'] = $this->ok('Education section is present.');

        $complete = 0;
        $validDateRows = 0;
        foreach ($education as $row) {
            $institution = $this->value($row['institution'] ?? null);
            $area = $this->value($row['area'] ?? null);
            if ($institution !== '' && $area !== '') {
                $complete++;
            }

            $start = $this->value($row['startDate'] ?? null);
            $end = $this->value($row['endDate'] ?? null);
            if (($start === '' || $this->isAtsFriendlyDate($start)) && ($end === '' || $this->isAtsFriendlyDate($end))) {
                $validDateRows++;
            }
        }

        $completeRatio = count($education) > 0 ? $complete / count($education) : 0;
        $points += (int) round(4 * min(1, $completeRatio));
        if ($completeRatio >= 0.9) {
            $checks['completeness'] = $this->ok('Education entries include institution and area of study.');
        } else {
            $checks['completeness'] = $this->warn('Include institution and study area for every entry.');
            $notes[] = 'Complete education entries with institution and field of study.';
        }

        $dateRatio = count($education) > 0 ? $validDateRows / count($education) : 0;
        $points += (int) round(2 * min(1, $dateRatio));
        if ($dateRatio < 1) {
            $checks['dates'] = $this->warn('Use consistent dates like YYYY-MM or YYYY-MM-DD.');
            $notes[] = 'Use consistent date formats in Education (YYYY-MM or YYYY-MM-DD).';
        } else {
            $checks['dates'] = $this->ok('Education date format is consistent.');
        }

        $max = 10;
        $points = min($max, $points);

        return [$points, $this->sectionFeedback($points, $max, $checks), $notes];
    }

    private function scoreSkills(array $skills): array
    {
        $points = 0;
        $notes = [];
        $checks = [];

        if (count($skills) === 0) {
            $checks['presence'] = $this->missing('Add a skills section with role-relevant keywords.');
            $notes[] = 'Add a skills section with role-relevant keywords.';
            return [0, $this->sectionFeedback(0, 10, $checks), $notes];
        }

        $points += 4;
        $checks['presence'] = $this->ok('Skills section is present.');

        $skillNames = array_values(array_filter(array_map(
            fn ($s) => $this->value($s['name'] ?? null),
            $skills
        )));

        $count = count($skillNames);
        if ($count >= 6) {
            $points += 4;
            $checks['count'] = $this->ok('Skills depth is strong.');
        } elseif ($count >= 3) {
            $points += 2;
            $checks['count'] = $this->warn('Add more relevant skills to improve keyword coverage.');
            $notes[] = 'Add more relevant skills to improve ATS keyword matching.';
        } else {
            $checks['count'] = $this->warn('Add more than 4 skills for better keyword coverage.');
            $notes[] = 'Expand your skills list to at least 5-8 targeted skills.';
        }

        $unique = count(array_unique(array_map(fn ($s) => mb_strtolower($s), $skillNames)));
        if ($count > 0 && $unique === $count) {
            $points += 2;
            $checks['uniqueness'] = $this->ok('Skills are distinct.');
        } else {
            $checks['uniqueness'] = $this->warn('Remove duplicate skills and keep names consistent.');
            $notes[] = 'Remove duplicate skills and standardize skill naming.';
        }

        $max = 10;
        $points = min($max, $points);

        return [$points, $this->sectionFeedback($points, $max, $checks), $notes];
    }

    private function scoreAts(array $resume): array
    {
        $points = 15;
        $notes = [];
        $checks = [];

        $invalidDates = 0;
        foreach ((array) ($resume['work'] ?? []) as $job) {
            $start = $this->value($job['startDate'] ?? null);
            $end = $this->value($job['endDate'] ?? null);
            if (($start !== '' && !$this->isAtsFriendlyDate($start)) || ($end !== '' && !$this->isAtsFriendlyDate($end))) {
                $invalidDates++;
            }
        }
        foreach ((array) ($resume['education'] ?? []) as $row) {
            $start = $this->value($row['startDate'] ?? null);
            $end = $this->value($row['endDate'] ?? null);
            if (($start !== '' && !$this->isAtsFriendlyDate($start)) || ($end !== '' && !$this->isAtsFriendlyDate($end))) {
                $invalidDates++;
            }
        }

        if ($invalidDates > 0) {
            $points -= min(4, $invalidDates);
            $checks['dates'] = $this->warn('Normalize dates (YYYY-MM or YYYY-MM-DD) for better ATS parsing.');
            $notes[] = 'Normalize date formats for ATS parsing.';
        } else {
            $checks['dates'] = $this->ok('Date formatting looks ATS-friendly.');
        }

        $bullets = [];
        foreach ((array) ($resume['work'] ?? []) as $job) {
            $bullets = array_merge($bullets, $this->extractBullets((array) $job));
        }

        if (count($bullets) > 0) {
            $avgLen = (int) round(array_sum(array_map(fn ($b) => mb_strlen(trim((string) $b)), $bullets)) / count($bullets));
            if ($avgLen > 240) {
                $points -= 3;
                $checks['readability'] = $this->warn('Bullets are long. Keep most bullets to 1-2 lines.');
                $notes[] = 'Shorten long bullets to improve scanability.';
            } else {
                $checks['readability'] = $this->ok('Bullet length is readable.');
            }

            $repeated = $this->findOverusedWords(mb_strtolower(implode(' ', $bullets)));
            if (!empty($repeated)) {
                $points -= 2;
                $checks['repetition'] = $this->warn('Reduce repeated terms: ' . implode(', ', array_slice($repeated, 0, 5)) . '.');
                $notes[] = 'Reduce repeated words in work bullets.';
            } else {
                $checks['repetition'] = $this->ok('Word variety is good.');
            }
        } else {
            $points -= 3;
            $checks['readability'] = $this->warn('Add concise bullets under work history for ATS readability.');
            $notes[] = 'Add bullet-based achievements under work history.';
        }

        [$spellingPenalty, $spellingCheck, $spellingNotes] = $this->scoreSpelling($resume);
        $points -= $spellingPenalty;
        $checks['spelling'] = $spellingCheck;
        $notes = array_merge($notes, $spellingNotes);

        $max = 15;
        $points = max(0, min($max, $points));

        return [$points, $this->sectionFeedback($points, $max, $checks), $notes];
    }

    private function sectionFeedback(int $points, int $max, array $checks): array
    {
        $ratio = $max > 0 ? $points / $max : 0;
        $message = match (true) {
            $ratio >= 0.85 => 'Strong section.',
            $ratio >= 0.65 => 'Good section with room for improvement.',
            $ratio >= 0.4 => 'Needs improvement.',
            default => 'Weak section. Prioritize fixes here.',
        };

        return [
            'score' => $points,
            'max' => $max,
            'message' => $message,
            'checks' => $checks,
        ];
    }

    private function extractBullets(array $job): array
    {
        $highlights = $job['highlights'] ?? [];
        if (is_array($highlights)) {
            $normalized = array_values(array_filter(array_map(fn ($h) => $this->value($h), $highlights)));
            if (!empty($normalized)) {
                return $normalized;
            }
        }

        $summary = $this->value($job['summary'] ?? null);
        if ($summary === '') {
            return [];
        }

        $parts = preg_split('/\r\n|\r|\n|\x{2022}|•/u', $summary);
        $parts = array_values(array_filter(array_map(fn ($b) => trim((string) $b), (array) $parts)));
        if (count($parts) > 1) {
            return $parts;
        }

        return [$summary];
    }

    private function startsWithActionVerb(string $text): bool
    {
        $firstWord = trim((string) strtok($text, " \t\n\r\0\x0B,.;:-"));
        return in_array($firstWord, $this->actionVerbs, true);
    }

    private function hasMetric(string $text): bool
    {
        return (bool) preg_match(
            '/(
                \d+(\.\d+)?\s?% |
                \$\s?\d[\d,]*(\.\d+)? |
                \b\d+(\.\d+)?\s?[x×]\b |
                \b\d+\s?(?:-|–|—|to)\s?\d+\b |
                \b\d+\s?(k|m|b|ms|s|sec|secs|mins?|minutes?|hours?|days?|weeks?|months?|years?|users?|clients?|tickets?|tasks?|stories?|prs?|pull\s?requests?)\b |
                \b\d{2,}\b
            )/ix',
            $text
        );
    }

    private function containsWeakPhrase(string $text): bool
    {
        foreach ($this->weakPhrases as $phrase) {
            if (str_contains($text, $phrase)) {
                return true;
            }
        }

        return false;
    }

    private function scoreSpelling(array $resume): array
    {
        $entries = $this->collectSpellCheckEntries($resume);
        if (empty($entries)) {
            return [0, $this->ok('No filled text columns were available for spell check.'), []];
        }

        $config = $this->languageToolConfig();
        if (!$config['enabled']) {
            return [
                0,
                [
                    'status' => 'warn',
                    'message' => 'LanguageTool is not configured. Spell check was skipped.',
                    'stats' => [
                        'checkedWords' => 0,
                        'misspelledWords' => 0,
                        'sample' => [],
                        'penalty' => 0,
                    ],
                    'fields' => [],
                    'provider' => $this->languageToolMeta($config, false, 'LanguageTool URL is not configured.'),
                ],
                ['LanguageTool is not configured. Spell check was skipped.'],
            ];
        }

        $fieldStats = [];
        $serviceAvailable = true;
        $errorMessage = null;

        foreach ($entries as $entry) {
            $path = (string) ($entry['path'] ?? '');
            $text = (string) ($entry['text'] ?? '');
            if ($path === '' || trim($text) === '') {
                continue;
            }

            $plainText = $this->visibleText($text);
            $tokens = $this->tokenizeForSpellCheck($plainText);
            if (empty($tokens)) {
                continue;
            }

            $fieldChecked = count($tokens);
            $fieldMisspelled = $this->checkSpellingWithLanguageTool($plainText, $config, $errorMessage);
            if ($errorMessage !== null) {
                $serviceAvailable = false;
                break;
            }

            $fieldMisspelledCount = count($fieldMisspelled);

            $fieldStats[$path] = [
                'checkedWords' => $fieldChecked,
                'misspelledWords' => $fieldMisspelledCount,
                'sample' => array_values(array_unique(array_slice($fieldMisspelled, 0, 5))),
            ];
        }

        if (!$serviceAvailable) {
            return [
                0,
                [
                    'status' => 'warn',
                    'message' => 'LanguageTool is unavailable. Spell check was skipped.',
                    'stats' => [
                        'checkedWords' => 0,
                        'misspelledWords' => 0,
                        'sample' => [],
                        'penalty' => 0,
                    ],
                    'fields' => [],
                    'provider' => $this->languageToolMeta($config, false, $errorMessage),
                ],
                ['LanguageTool is unavailable. Spell check was skipped.'],
            ];
        }

        $checkedWords = array_sum(array_map(
            fn (array $field) => (int) ($field['checkedWords'] ?? 0),
            $fieldStats
        ));

        $misspelledWords = array_sum(array_map(
            fn (array $field) => (int) ($field['misspelledWords'] ?? 0),
            $fieldStats
        ));

        $allMisspelled = [];
        foreach ($fieldStats as $field) {
            $allMisspelled = array_merge($allMisspelled, (array) ($field['sample'] ?? []));
        }
        $allMisspelled = array_values(array_unique(array_filter($allMisspelled)));

        if ($checkedWords === 0) {
            return [0, $this->ok('No spell-checkable words detected in filled text columns.'), []];
        }

        if ($misspelledWords === 0) {
            return [
                0,
                [
                    'status' => 'good',
                    'message' => "Spelling check passed on {$checkedWords} words.",
                    'stats' => [
                        'checkedWords' => $checkedWords,
                        'misspelledWords' => 0,
                        'sample' => [],
                        'penalty' => 0,
                    ],
                    'fields' => $fieldStats,
                    'provider' => $this->languageToolMeta($config, true),
                ],
                [],
            ];
        }

        $ratio = $misspelledWords / $checkedWords;
        $penalty = match (true) {
            $ratio <= 0.03 => 1,
            $ratio <= 0.06 => 2,
            $ratio <= 0.10 => 3,
            $ratio <= 0.15 => 4,
            default => 5,
        };
        if ($checkedWords < 25) {
            $penalty = min($penalty, 2);
        }
        if ($checkedWords < 12) {
            $penalty = min($penalty, 1);
        }
        $penalty = min(4, $penalty);

        $sample = array_values(array_unique(array_slice($allMisspelled, 0, 8)));
        $note = 'Spell check found possible misspellings. Review words like: ' . implode(', ', $sample) . '.';

        return [
            $penalty,
            [
                'status' => 'warn',
                'message' => "Possible misspellings found ({$misspelledWords}/{$checkedWords} words).",
                'stats' => [
                    'checkedWords' => $checkedWords,
                    'misspelledWords' => $misspelledWords,
                    'sample' => $sample,
                    'penalty' => $penalty,
                ],
                'fields' => $fieldStats,
                'provider' => $this->languageToolMeta($config, true),
            ],
            [$note],
        ];
    }

    private function collectSpellCheckEntries(array $resume): array
    {
        $entries = [];
        $push = function (string $path, mixed $value) use (&$entries): void {
            if ($value === null) {
                return;
            }

            if (!is_string($value)) {
                return;
            }

            $trimmed = trim($value);
            if ($trimmed === '') {
                return;
            }

            $entries[] = [
                'path' => $path,
                'text' => $trimmed,
            ];
        };

        $basics = (array) ($resume['basics'] ?? []);
        if (array_key_exists('summary', $basics)) {
            $push('basics.summary', $basics['summary']);
        }

        foreach ((array) ($resume['work'] ?? []) as $index => $job) {
            if (!is_array($job)) {
                continue;
            }

            if (array_key_exists('summary', $job)) {
                $push("work.{$index}.summary", $job['summary']);
            }

            if (!array_key_exists('highlights', $job) || $job['highlights'] === null) {
                continue;
            }

            if (is_array($job['highlights'])) {
                foreach ($job['highlights'] as $hIndex => $highlight) {
                    $push("work.{$index}.highlights.{$hIndex}", $highlight);
                }
            } elseif (is_string($job['highlights'])) {
                $push("work.{$index}.highlights", $job['highlights']);
            }
        }

        foreach ((array) ($resume['references'] ?? []) as $index => $reference) {
            if (is_array($reference) && array_key_exists('reference', $reference)) {
                $push("references.{$index}.reference", $reference['reference']);
            }
        }

        $bodySections = [
            'certificate' => 'certificate.body',
            'accomplishment' => 'accomplishment.body',
            'affiliation' => 'affiliation.body',
            'interest' => 'interest.body',
            'volunteer' => 'volunteer.body',
            'project' => 'project.body',
        ];

        foreach ($bodySections as $section => $path) {
            $sectionValue = $resume[$section] ?? null;
            if (is_array($sectionValue) && array_key_exists('body', $sectionValue)) {
                $push($path, $sectionValue['body']);
            }
        }

        return $entries;
    }

    private function tokenizeForSpellCheck(string $text): array
    {
        $text = $this->visibleText($text);
        $text = str_replace(["\u{2019}", "\u{2018}"], "'", $text);
        $text = str_replace(['-', '–', '—'], ' ', $text);

        $parts = preg_split("/[^\\p{L}'`]+/u", $text, -1, PREG_SPLIT_NO_EMPTY);
        $tokens = [];

        foreach ((array) $parts as $part) {
            $word = mb_strtolower(trim((string) $part, "'`"));
            if ($word === '') {
                continue;
            }

            if (str_ends_with($word, "'s")) {
                $word = substr($word, 0, -2);
            }

            if (mb_strlen($word) < 3) {
                continue;
            }

            if (preg_match('/\\d/u', $word)) {
                continue;
            }

            if (in_array($word, $this->spellIgnoreWords, true)) {
                continue;
            }

            $tokens[] = $word;
        }

        return $tokens;
    }

    private function visibleText(string $text): string
    {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Preserve semantic separators so adjacent HTML blocks don't merge into one token.
        $withBreaks = preg_replace(
            '/<(?:br\\s*\\/?>|\\/p\\s*>|p\\b[^>]*>|\\/div\\s*>|div\\b[^>]*>|\\/li\\s*>|li\\b[^>]*>|\\/h[1-6]\\s*>|h[1-6]\\b[^>]*>|\\/tr\\s*>|tr\\b[^>]*>|\\/td\\s*>|td\\b[^>]*>)/i',
            ' ',
            $decoded
        );

        $plain = strip_tags((string) $withBreaks);
        return preg_replace('/\\s+/u', ' ', $plain) ?? $plain;
    }

    private function languageToolConfig(): array
    {
        $url = trim((string) $this->safeConfig('services.languagetool.url', getenv('LANGUAGETOOL_URL') ?: ''));
        $language = trim((string) $this->safeConfig('services.languagetool.language', getenv('LANGUAGETOOL_LANGUAGE') ?: 'en-US'));
        $timeout = (float) $this->safeConfig('services.languagetool.timeout', getenv('LANGUAGETOOL_TIMEOUT') ?: 6);
        $enabled = $url !== '';

        return [
            'enabled' => $enabled,
            'url' => $enabled ? rtrim($url, '/') : '',
            'language' => $language !== '' ? $language : 'en-US',
            'timeout' => $timeout > 0 ? $timeout : 6.0,
        ];
    }

    private function safeConfig(string $key, mixed $default = null): mixed
    {
        if (!function_exists('config')) {
            return $default;
        }

        try {
            return config($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }

    private function languageToolMeta(array $config, bool $available, ?string $error = null): array
    {
        return [
            'name' => 'collabora/languagetool',
            'url' => (string) ($config['url'] ?? ''),
            'language' => (string) ($config['language'] ?? 'en-US'),
            'available' => $available,
            'error' => $error,
        ];
    }

    private function checkSpellingWithLanguageTool(string $text, array $config, ?string &$error = null): array
    {
        $error = null;
        $url = rtrim((string) ($config['url'] ?? ''), '/') . '/v2/check';
        $timeout = (float) ($config['timeout'] ?? 6.0);
        $language = (string) ($config['language'] ?? 'en-US');

        $payload = [
            'language' => $language,
            'text' => $text,
        ];

        try {
            $response = Http::timeout($timeout)
                ->retry(1, 200, throw: false)
                ->asForm()
                ->post($url, $payload);
        } catch (\Throwable $e) {
            return $this->checkSpellingWithLanguageToolCurl($url, $payload, $timeout, $error, $e->getMessage());
        }

        if (!$response->successful()) {
            return $this->checkSpellingWithLanguageToolCurl($url, $payload, $timeout, $error, 'LanguageTool HTTP ' . $response->status());
        }

        $matches = data_get($response->json(), 'matches', []);
        if (!is_array($matches)) {
            return $this->checkSpellingWithLanguageToolCurl($url, $payload, $timeout, $error, 'LanguageTool returned invalid response.');
        }

        $misspelled = [];
        foreach ($matches as $match) {
            if (!is_array($match) || !$this->isSpellingMatch($match)) {
                continue;
            }

            $token = $this->extractTokenFromLanguageToolMatch($text, $match);
            $normalized = $this->normalizeSpellToken($token);
            if ($normalized === '') {
                continue;
            }

            $misspelled[] = $normalized;
        }

        return $misspelled;
    }

    private function checkSpellingWithLanguageToolCurl(
        string $url,
        array $payload,
        float $timeout,
        ?string &$error,
        ?string $fallbackError = null
    ): array {
        if (!function_exists('curl_init')) {
            $error = $fallbackError ?? 'LanguageTool request failed and cURL is unavailable.';
            return [];
        }

        $ch = curl_init($url);
        if ($ch === false) {
            $error = $fallbackError ?? 'Failed to initialize cURL.';
            return [];
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => (int) ceil($timeout),
            CURLOPT_CONNECTTIMEOUT => (int) ceil($timeout),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query($payload),
        ]);

        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($body === false || $httpCode < 200 || $httpCode >= 300) {
            $error = $curlErr !== '' ? $curlErr : ($fallbackError ?? 'LanguageTool HTTP ' . $httpCode);
            return [];
        }

        $decoded = json_decode((string) $body, true);
        $matches = data_get($decoded, 'matches', []);
        if (!is_array($matches)) {
            $error = $fallbackError ?? 'LanguageTool returned invalid response.';
            return [];
        }

        $misspelled = [];
        foreach ($matches as $match) {
            if (!is_array($match) || !$this->isSpellingMatch($match)) {
                continue;
            }

            $token = $this->extractTokenFromLanguageToolMatch((string) ($payload['text'] ?? ''), $match);
            $normalized = $this->normalizeSpellToken($token);
            if ($normalized === '') {
                continue;
            }

            $misspelled[] = $normalized;
        }

        $error = null;
        return $misspelled;
    }

    private function isSpellingMatch(array $match): bool
    {
        $issueType = mb_strtolower((string) data_get($match, 'rule.issueType', ''));
        if (in_array($issueType, ['misspelling', 'typographical'], true)) {
            return true;
        }

        $ruleId = mb_strtolower((string) data_get($match, 'rule.id', ''));
        return str_contains($ruleId, 'morfologik') || str_contains($ruleId, 'spell');
    }

    private function extractTokenFromLanguageToolMatch(string $text, array $match): string
    {
        $offset = (int) data_get($match, 'offset', -1);
        $length = (int) data_get($match, 'length', 0);

        if ($offset >= 0 && $length > 0) {
            $slice = mb_substr($text, $offset, $length);
            if (is_string($slice) && trim($slice) !== '') {
                return $slice;
            }

            $slice = substr($text, $offset, $length);
            if (is_string($slice) && trim($slice) !== '') {
                return $slice;
            }
        }

        $contextText = (string) data_get($match, 'context.text', '');
        $contextOffset = (int) data_get($match, 'context.offset', -1);
        $contextLength = (int) data_get($match, 'context.length', 0);
        if ($contextText !== '' && $contextOffset >= 0 && $contextLength > 0) {
            $slice = mb_substr($contextText, $contextOffset, $contextLength);
            if (is_string($slice) && trim($slice) !== '') {
                return $slice;
            }

            $slice = substr($contextText, $contextOffset, $contextLength);
            if (is_string($slice) && trim($slice) !== '') {
                return $slice;
            }
        }

        return '';
    }

    private function normalizeSpellToken(string $token): string
    {
        $part = trim($token);
        if ($part === '') {
            return '';
        }

        $words = preg_split("/[^\\p{L}'`]+/u", $part, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($words) || empty($words)) {
            return '';
        }

        $word = mb_strtolower(trim((string) $words[0], "'`"));
        if ($word === '') {
            return '';
        }

        if (str_ends_with($word, "'s")) {
            $word = substr($word, 0, -2);
        }
        if ($word === '' || mb_strlen($word) < 3) {
            return '';
        }
        if (preg_match('/\\d/u', $word)) {
            return '';
        }
        if (in_array($word, $this->spellIgnoreWords, true)) {
            return '';
        }

        return $word;
    }

    private function findOverusedWords(string $text): array
    {
        $words = preg_split('/\W+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $freq = [];

        foreach ($words as $word) {
            $word = mb_strtolower((string) $word);
            if (mb_strlen($word) < 4 || in_array($word, $this->stopWords, true)) {
                continue;
            }
            $freq[$word] = ($freq[$word] ?? 0) + 1;
        }

        arsort($freq);

        return array_keys(array_filter($freq, fn ($count) => $count >= 6));
    }

    private function isAtsFriendlyDate(string $value): bool
    {
        $value = trim($value);
        if ($value === '') {
            return true;
        }

        $normalized = mb_strtolower($value);
        if (in_array($normalized, ['present', 'current', 'now'], true)) {
            return true;
        }

        return (bool) preg_match('/^\d{4}(-\d{2})?(-\d{2})?$/', $value);
    }

    private function value(mixed $value): string
    {
        if (is_string($value) || is_numeric($value)) {
            return trim((string) $value);
        }

        return '';
    }

    private function grade(int $score): string
    {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= self::PASSING_SCORE => 'Strong',
            $score >= 60 => 'Good',
            $score >= 45 => 'Needs Work',
            default => 'Weak',
        };
    }

    private function ok(string $message): array
    {
        return ['status' => 'good', 'message' => $message];
    }

    private function warn(string $message): array
    {
        return ['status' => 'warn', 'message' => $message];
    }

    private function missing(string $message): array
    {
        return ['status' => 'missing', 'message' => $message];
    }
}
