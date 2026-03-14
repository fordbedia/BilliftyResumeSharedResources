{{-- shared-resources/src/Modules/Builder/resources/views/templates/minimal.blade.php --}}

<?php
    $resume = $resume ?? [];

    // =========================
    // New sections (rich text bodies)
    // =========================
    $accomplishmentActive = (bool) data_get($resume, 'accomplishment.is_active');
    $affiliationActive    = (bool) data_get($resume, 'affiliation.is_active');
    $certificateActive    = (bool) data_get($resume, 'certificate.is_active');
    $interestActive       = (bool) data_get($resume, 'interest.is_active');
    $volunteerActive      = (bool) data_get($resume, 'volunteer.is_active');
    $projectActive        = (bool) data_get($resume, 'project.is_active');

    $accomplishmentBody = (string) data_get($resume, 'accomplishment.body', '');
    $affiliationBody    = (string) data_get($resume, 'affiliation.body', '');
    $certificateBody    = (string) data_get($resume, 'certificate.body', '');
    $interestBody       = (string) data_get($resume, 'interest.body', '');
    $volunteerBody      = (string) data_get($resume, 'volunteer.body', '');
    $projectBody        = (string) data_get($resume, 'project.body', '');

    $hasAccomplishment = $accomplishmentActive && trim(strip_tags($accomplishmentBody)) !== '';
    $hasAffiliation    = $affiliationActive && trim(strip_tags($affiliationBody)) !== '';
    $hasCertificate    = $certificateActive && trim(strip_tags($certificateBody)) !== '';
    $hasInterest       = $interestActive && trim(strip_tags($interestBody)) !== '';
    $hasVolunteer      = $volunteerActive && trim(strip_tags($volunteerBody)) !== '';
    $hasProject        = $projectActive && trim(strip_tags($projectBody)) !== '';

    // =========================
    // Sidebar extras (new structure)
    // =========================
    $languagesActive   = (bool) data_get($resume, 'languages.is_active');
    $sidebarLanguages  = (array) data_get($resume, 'languages.languages', []);

    $websitesActive    = (bool) data_get($resume, 'websites.is_active');
    $websites          = (array) data_get($resume, 'websites.websites', []);

    $hasLanguages = $languagesActive && !empty($sidebarLanguages);
    $hasWebsites  = $websitesActive && !empty($websites);

    $basics = (array) ($resume['basics'] ?? []);
    $previewColorScheme = $previewColorScheme ?? null;
    $colorScheme = $previewColorScheme ?? data_get($resume, 'colorScheme');

    $primaryColor = '#6366F1';
    $gradientTop = '#6366F1';
    $gradientBottom = '#818CF8';

    if (is_string($colorScheme)) {
        $candidate = trim($colorScheme);
        if (preg_match('/^(#[0-9a-fA-F]{3,8}|rgba?\([^)]+\)|hsla?\([^)]+\))$/', $candidate)) {
            $primaryColor = $candidate;
            $gradientTop = $candidate;
            $gradientBottom = $candidate;

            if (preg_match('/^hsla?\(\s*([0-9]+(?:\.[0-9]+)?)\s*(?:deg)?(?:\s*,\s*|\s+)([0-9]+(?:\.[0-9]+)?)%\s*(?:\s*,\s*|\s+)([0-9]+(?:\.[0-9]+)?)%(?:\s*\/\s*([0-9.]+%?))?\s*\)$/i', $candidate, $m)) {
                $h = (float) $m[1];
                $s = (float) $m[2];
                $l = (float) $m[3];
                $a = $m[4] ?? null;

                // Create a same-hue lighter companion color for the gradient.
                $topL = max(12, min(75, $l));
                $bottomL = min(88, $topL + 12);

                if ($a !== null && $a !== '') {
                    $gradientTop = sprintf('hsla(%s %s%% %s%% / %s)', round($h, 2), round($s, 2), round($topL, 2), $a);
                    $gradientBottom = sprintf('hsla(%s %s%% %s%% / %s)', round($h, 2), round($s, 2), round($bottomL, 2), $a);
                } else {
                    $gradientTop = sprintf('hsl(%s %s%% %s%%)', round($h, 2), round($s, 2), round($topL, 2));
                    $gradientBottom = sprintf('hsl(%s %s%% %s%%)', round($h, 2), round($s, 2), round($bottomL, 2));
                }
            }
        }
    }

    // Always use a validated/normalized color string in styles below.
    $colorScheme = $primaryColor;

    // NOTE: assumes you have this helper available globally (as you already use it)
    $dynamicTextColor = contrastTextFromHsl($primaryColor);

    // =========================
    // Small helpers (safe)
    // =========================
    $safeText = function ($v) {
        if (is_null($v)) return '';
        if (is_string($v) || is_numeric($v)) return trim((string) $v);
        return '';
    };

    $fmtDateRange = function ($start, $end) use ($safeText) {
        $start = $safeText($start);
        $end   = $safeText($end);

        if ($start === '' && $end === '') return '';
        if ($start !== '' && $end === '') return $start . ' - Present';
        if ($start === '' && $end !== '') return $end;
        return $start . ' - ' . $end;
    };

    $normalizeWebsite = function ($site) use ($safeText) {
        $label = 'Website';
        $url   = '';

        if (is_array($site)) {
            $label = $safeText(data_get($site, 'label'))
                ?: $safeText(data_get($site, 'name'))
                ?: $safeText(data_get($site, 'title'))
                ?: 'Website';

            $url = $safeText(data_get($site, 'url'))
                ?: $safeText(data_get($site, 'value'))
                ?: $safeText(data_get($site, 'link'))
                ?: '';
        } else {
            $url = $safeText($site);
        }

        return ['label' => $label, 'url' => $url];
    };

    $normalizeLanguage = function ($lang) use ($safeText) {
        $name = '';
        $meta = '';

        if (is_array($lang)) {
            $name = $safeText(data_get($lang, 'language')) ?: $safeText(data_get($lang, 'name')) ?: '';
            $meta = $safeText(data_get($lang, 'fluency')) ?: $safeText(data_get($lang, 'level')) ?: '';
        } else {
            $name = $safeText($lang);
        }

        return ['name' => $name, 'meta' => $meta];
    };

    $toInitials = function ($name) use ($safeText) {
        $name = $safeText($name);
        if ($name === '') return 'YN';
        $parts = preg_split('/\s+/', $name);
        $first = strtoupper(substr($parts[0] ?? 'Y', 0, 1));
        $last  = strtoupper(substr($parts[count($parts) - 1] ?? 'N', 0, 1));
        return $first . $last;
    };

    // UPDATED: expert -> 100%
    $skillPercent = function ($level) use ($safeText) {
        $l = strtolower($safeText($level));
        if ($l === '') return 70;
        if (strpos($l, 'expert') !== false) return 100;   // <- full bar
        if (strpos($l, 'advanced') !== false) return 78;
        if (strpos($l, 'intermediate') !== false) return 55;
        if (strpos($l, 'beginner') !== false) return 35;
        if (is_numeric($l)) {
            $n = (int) $l;
            if ($n <= 5) return max(10, min(100, $n * 20));
            if ($n <= 10) return max(10, min(100, $n * 10));
            return max(10, min(100, $n));
        }
        return 70;
    };

    $compactLocation = function ($city, $region) use ($safeText) {
        $city   = $safeText($city);
        $region = $safeText($region);
        if ($city === '' && $region === '') return '';
        if ($city !== '' && $region !== '') return $city . ', ' . $region;
        return $city ?: $region;
    };

    // Header bits
    $label  = $safeText($basics['label'] ?? '');
    $city   = $safeText(data_get($basics, 'location.city'));
    $region = $safeText(data_get($basics, 'location.region'));
    $email  = $safeText($basics['email'] ?? '');
    $phone  = $safeText($basics['phone'] ?? '');
    $url    = $safeText($basics['url'] ?? '');

    $photo  = $safeText(data_get($basics, 'imageUrl'))
        ?: $safeText(data_get($basics, 'picture'))
        ?: $safeText(data_get($basics, 'photo'))
        ?: '';

    $displayLocation = $compactLocation($city, $region);

    // Work / Education / Certificates arrays (JSON Resume style)
    $workItems = (array) data_get($resume, 'work', []);
    $eduItems  = (array) data_get($resume, 'education', []);
    $certItems = (array) data_get($resume, 'certificates', []);
    $refItems  = (array) data_get($resume, 'references', []);

    // Local order resolution keeps this template stable even when incoming keys use aliases.
    $sectionOrderDefaults = [
        'basics',
        'work',
        'education',
        'skills',
        'references',
        'additional_information',
        'for_us_candidates',
    ];
    $sectionOrderAliases = [
        'basic' => 'basics',
        'basic_info' => 'basics',
        'personal_info' => 'basics',
        'profile' => 'basics',
        'experience' => 'work',
        'certificate' => 'additional_information',
        'certificates' => 'additional_information',
        'accomplishment' => 'additional_information',
        'accomplishments' => 'additional_information',
        'project' => 'for_us_candidates',
        'projects' => 'for_us_candidates',
        'volunteer' => 'for_us_candidates',
        'affiliation' => 'for_us_candidates',
        'affiliations' => 'for_us_candidates',
        'interest' => 'for_us_candidates',
        'interests' => 'for_us_candidates',
        'website' => 'for_us_candidates',
        'websites' => 'for_us_candidates',
    ];
    $incomingSectionOrder = (array) data_get($resume, 'sectionOrder', data_get($resume, 'section_order', []));
    $sectionOrder = [];
    foreach ($incomingSectionOrder as $sectionKey) {
        if (!is_string($sectionKey)) {
            continue;
        }
        $normalizedKey = strtolower(trim($sectionKey));
        $normalizedKey = str_replace(['-', ' '], '_', $normalizedKey);
        $normalizedKey = $sectionOrderAliases[$normalizedKey] ?? $normalizedKey;

        if (!in_array($normalizedKey, $sectionOrderDefaults, true) || in_array($normalizedKey, $sectionOrder, true)) {
            continue;
        }
        $sectionOrder[] = $normalizedKey;
    }
    foreach ($sectionOrderDefaults as $defaultKey) {
        if (!in_array($defaultKey, $sectionOrder, true)) {
            $sectionOrder[] = $defaultKey;
        }
    }
    $sectionOrderPriority = array_flip($sectionOrder);
    $sectionOrderFor = function (string $key) use ($sectionOrderPriority): int {
        return ((int) ($sectionOrderPriority[$key] ?? 999)) + 1;
    };
    $sectionOrderStyle = function (string $key) use ($sectionOrderFor): string {
        return 'order: ' . $sectionOrderFor($key) . ';';
    };
?>

<style>
    @page { margin: 0; }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        padding: 0;
        color: #111827;
        background: #ffffff;
        font-family: Calibri, Arial, Helvetica, Tahoma, Verdana, sans-serif;
        font-size: 11pt;
        line-height: 1.45;
    }

    .sheet {
        width: 210mm;
        margin: 0 auto;
        padding: 12mm;
        display: flex;
        flex-direction: column;
    }

    .row {
        display: block;
        width: 100%;
        margin: 0 0 14px 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
    }

    .row:last-child {
        margin-bottom: 0;
        border-bottom: 0;
    }

    .name {
        margin: 0;
        font-size: 22pt;
        line-height: 1.1;
        font-weight: 700;
        color: #111827;
    }

    .headline {
        margin-top: 4px;
        font-size: 11pt;
        color: {{ $colorScheme }};
        font-weight: 600;
    }

    .contact-line,
    .meta-line {
        margin-top: 8px;
        color: #374151;
    }

    .contact-line a,
    .plain-link {
        color: #374151;
        text-decoration: none;
    }

    .section-title {
        margin: 0 0 8px 0;
        font-size: 11pt;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: {{ $colorScheme }};
    }

    .item {
        margin: 0 0 10px 0;
    }

    .item:last-child {
        margin-bottom: 0;
    }

    .item-title {
        margin: 0;
        font-size: 11pt;
        font-weight: 700;
        color: #111827;
    }

    .item-sub {
        margin-top: 2px;
        color: #374151;
    }

    .item-meta {
        margin-top: 2px;
        font-size: 11pt;
        color: #6b7280;
    }

    .bullets {
        margin: 6px 0 0 16px;
        padding: 0;
        color: #1f2937;
    }

    .bullets li { margin: 0 0 5px 0; }

    .skills-wrap {
        margin: 0;
        padding: 0;
    }

    .skill-line {
        margin: 0 0 8px 0;
    }

    .skill-name {
        margin-bottom: 3px;
        font-weight: 600;
        color: #111827;
    }

    .skill-track {
        width: 100%;
        height: 5px;
        background: #e5e7eb;
    }

    .skill-fill {
        height: 100%;
        background: {{ $colorScheme }};
    }

    .list {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .list li {
        margin: 0 0 6px 0;
    }

    .rich p { margin: 0 0 8px 0; }
    .rich ul, .rich ol { margin: 8px 0 0 18px; }
    .rich li { margin: 0 0 6px 0; }
</style>

<div class="sheet">
    <section class="row" style="{{ $sectionOrderStyle('basics') }}">
        <h1 class="name">{{ $safeText(data_get($basics, 'name')) !== '' ? $safeText(data_get($basics, 'name')) : 'Your Name' }}</h1>

        @if($label !== '')
            <div class="headline">{{ $label }}</div>
        @endif

        @if(!empty($basics['summary']))
            <div class="meta-line rich" style="margin-top: 10px;">{!! $basics['summary'] !!}</div>
        @endif

        @if($email !== '' || $phone !== '' || $displayLocation !== '' || $url !== '')
            <div class="contact-line">
                @if($email !== '')<span>{{ $email }}</span>@endif
                @if($email !== '' && $phone !== '') <span> | </span> @endif
                @if($phone !== '')<span>{{ $phone }}</span>@endif
                @if(($email !== '' || $phone !== '') && $displayLocation !== '') <span> | </span> @endif
                @if($displayLocation !== '')<span>{{ $displayLocation }}</span>@endif
                @if(($email !== '' || $phone !== '' || $displayLocation !== '') && $url !== '') <span> | </span> @endif
                @if($url !== '')<a class="plain-link" href="{{ $url }}">{{ $url }}</a>@endif
            </div>
        @endif
    </section>

    @if($hasWebsites)
        <section class="row" style="{{ $sectionOrderStyle('for_us_candidates') }}">
            <h2 class="section-title">Websites</h2>
            <ul class="list">
                @foreach($websites as $site)
                    @php $w = $normalizeWebsite($site); @endphp
                    @if($w['url'] !== '')
                        <li>
                            <strong>{{ $w['label'] }}:</strong>
                            <a class="plain-link" href="{{ $w['url'] }}">{{ $w['url'] }}</a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </section>
    @endif

    @if(!empty($resume['skills']))
        <section class="row" style="{{ $sectionOrderStyle('skills') }}">
            <h2 class="section-title">Skills</h2>

            @if(!empty(data_get($resume, 'skills.body')))
                <div class="rich">{!! data_get($resume, 'skills.body') !!}</div>
            @elseif(!empty(data_get($resume, 'skills.skills')))
                <div class="skills-wrap">
                    @foreach((array) data_get($resume, 'skills.skills', []) as $skill)
                        @if(is_array($skill))
                            @php
                                $skillName = $safeText(data_get($skill, 'name'));
                                $skillLevel = $safeText(data_get($skill, 'level'));
                                $pct = $skillPercent($skillLevel);
                            @endphp
                            @if($skillName !== '')
                                <div class="skill-line">
                                    <div class="skill-name">{{ $skillName }}@if($skillLevel !== '') - {{ $skillLevel }}@endif</div>
                                    <div class="skill-track"><div class="skill-fill" style="width: {{ $pct }}%;"></div></div>
                                </div>
                            @endif
                        @elseif(is_string($skill) && trim($skill) !== '')
                            <div class="skill-line">
                                <div class="skill-name">{{ $skill }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    @if(!empty($workItems))
        <section class="row" style="{{ $sectionOrderStyle('work') }}">
            <h2 class="section-title">Work Experience</h2>
            @foreach($workItems as $work)
                @if(is_array($work))
                    @php
                        $position = $safeText(data_get($work, 'position'));
                        $company  = $safeText(data_get($work, 'name'));
                        $range = $fmtDateRange(data_get($work, 'startDate'), data_get($work, 'endDate'));
                        $workSummary = (string) data_get($work, 'summary', '');
                        $highs = (array) data_get($work, 'highlights', []);
                    @endphp
                    @if($position !== '' || $company !== '' || $range !== '' || trim(strip_tags($workSummary)) !== '' || !empty($highs))
                        <article class="item">
                            <p class="item-title">{{ $position !== '' ? $position : 'Role' }}</p>
                            <div class="item-sub">{{ $company !== '' ? $company : '' }}</div>
                            @if($range !== '')<div class="item-meta">{{ $range }}</div>@endif

                            @if(trim(strip_tags($workSummary)) !== '')
                                <div class="rich" style="margin-top: 6px;">{!! $workSummary !!}</div>
                            @endif

                            @if(!empty($highs))
                                <ul class="bullets">
                                    @foreach($highs as $h)
                                        @if(is_string($h) && trim($h) !== '')<li>{{ $h }}</li>@endif
                                    @endforeach
                                </ul>
                            @endif
                        </article>
                    @endif
                @endif
            @endforeach
        </section>
    @endif

    @if(!empty($eduItems))
        <section class="row" style="{{ $sectionOrderStyle('education') }}">
            <h2 class="section-title">Education</h2>
            @foreach($eduItems as $edu)
                @if(is_array($edu))
                    @php
                        $institution = $safeText(data_get($edu, 'institution'));
                        $studyType   = $safeText(data_get($edu, 'studyType'));
                        $area        = $safeText(data_get($edu, 'area'));
                        $score       = $safeText(data_get($edu, 'score'));
                        $range       = $fmtDateRange(data_get($edu, 'startDate'), data_get($edu, 'endDate'));
                        $degree      = trim($studyType . ($area !== '' ? ' in ' . $area : ''));
                    @endphp
                    @if($institution !== '' || $degree !== '' || $range !== '' || $score !== '')
                        <article class="item">
                            <p class="item-title">{{ $degree !== '' ? $degree : 'Education' }}</p>
                            @if($institution !== '')<div class="item-sub">{{ $institution }}</div>@endif
                            @if($range !== '')<div class="item-meta">{{ $range }}</div>@endif
                            @if($score !== '')<div class="item-meta">GPA: {{ $score }}</div>@endif
                        </article>
                    @endif
                @endif
            @endforeach
        </section>
    @endif

    @if(!empty($certItems) || $hasCertificate)
        <section class="row" style="{{ $sectionOrderStyle('additional_information') }}">
            <h2 class="section-title">Certificates</h2>

            @if(!empty($certItems))
                @foreach($certItems as $cert)
                    @if(is_array($cert))
                        @php
                            $cname  = $safeText(data_get($cert, 'name'));
                            $issuer = $safeText(data_get($cert, 'issuer'));
                            $date   = $safeText(data_get($cert, 'date'));
                            $curl   = $safeText(data_get($cert, 'url'));
                        @endphp
                        @if($cname !== '' || $issuer !== '' || $date !== '' || $curl !== '')
                            <article class="item">
                                <p class="item-title">{{ $cname !== '' ? $cname : 'Certificate' }}</p>
                                @if($issuer !== '')<div class="item-sub">{{ $issuer }}</div>@endif
                                @if($date !== '')<div class="item-meta">{{ $date }}</div>@endif
                                @if($curl !== '')<div class="item-meta"><a class="plain-link" href="{{ $curl }}">{{ $curl }}</a></div>@endif
                            </article>
                        @endif
                    @endif
                @endforeach
            @elseif($hasCertificate)
                <div class="rich">{!! $certificateBody !!}</div>
            @endif
        </section>
    @endif

    @if($hasProject)
        <section class="row" style="{{ $sectionOrderStyle('for_us_candidates') }}">
            <h2 class="section-title">Projects</h2>
            <div class="rich">{!! $projectBody !!}</div>
        </section>
    @endif

    @if($hasAccomplishment)
        <section class="row" style="{{ $sectionOrderStyle('additional_information') }}">
            <h2 class="section-title">Accomplishments</h2>
            <div class="rich">{!! $accomplishmentBody !!}</div>
        </section>
    @endif

    @if($hasVolunteer)
        <section class="row" style="{{ $sectionOrderStyle('for_us_candidates') }}">
            <h2 class="section-title">Volunteer</h2>
            <div class="rich">{!! $volunteerBody !!}</div>
        </section>
    @endif

    @if($hasAffiliation)
        <section class="row" style="{{ $sectionOrderStyle('for_us_candidates') }}">
            <h2 class="section-title">Affiliations</h2>
            <div class="rich">{!! $affiliationBody !!}</div>
        </section>
    @endif

    @if($hasInterest)
        <section class="row" style="{{ $sectionOrderStyle('for_us_candidates') }}">
            <h2 class="section-title">Interests</h2>
            <div class="rich">{!! $interestBody !!}</div>
        </section>
    @endif

    @if($hasLanguages)
        <section class="row" style="{{ $sectionOrderStyle('additional_information') }}">
            <h2 class="section-title">Languages</h2>
            <ul class="list">
                @foreach($sidebarLanguages as $lang)
                    @php $l = $normalizeLanguage($lang); @endphp
                    @if($l['name'] !== '')
                        <li>
                            <strong>{{ $l['name'] }}</strong>@if($l['meta'] !== '') - {{ $l['meta'] }}@endif
                        </li>
                    @endif
                @endforeach
            </ul>
        </section>
    @endif

    @if(!empty($refItems))
        <section class="row" style="border-bottom: 0; padding-bottom: 0; {{ $sectionOrderStyle('references') }}">
            <h2 class="section-title">References</h2>
            @foreach($refItems as $r)
                @if(is_array($r))
                    @php
                        $refName = $safeText(data_get($r, 'name'));
                        $refBody = (string) data_get($r, 'reference', '');
                        $refTitle = $safeText(data_get($r, 'title')) ?: $safeText(data_get($r, 'position'));
                        $refCompany = $safeText(data_get($r, 'company')) ?: $safeText(data_get($r, 'organization'));
                        $refEmail = $safeText(data_get($r, 'email'));
                        $refPhone = $safeText(data_get($r, 'phone'));
                    @endphp
                    @if($refName !== '' || trim(strip_tags($refBody)) !== '' || $refTitle !== '' || $refCompany !== '' || $refEmail !== '' || $refPhone !== '')
                        <article class="item">
                            <p class="item-title">{{ $refName !== '' ? $refName : 'Reference' }}</p>
                            @if($refTitle !== '' || $refCompany !== '')
                                <div class="item-sub">{{ trim(($refTitle !== '' ? $refTitle : '') . ($refTitle !== '' && $refCompany !== '' ? ', ' : '') . ($refCompany !== '' ? $refCompany : '')) }}</div>
                            @endif
                            @if($refEmail !== '')<div class="item-meta">{{ $refEmail }}</div>@endif
                            @if($refPhone !== '')<div class="item-meta">{{ $refPhone }}</div>@endif
                            @if(trim(strip_tags($refBody)) !== '')<div class="rich" style="margin-top: 6px;">{!! $refBody !!}</div>@endif
                        </article>
                    @endif
                @endif
            @endforeach
        </section>
    @endif
</div>
