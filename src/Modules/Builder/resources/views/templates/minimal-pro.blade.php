{{-- shared-resources/src/Modules/Builder/resources/views/templates/minimal-two.blade.php --}}

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
?>

<style>
    @page { margin: 0; }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        padding: 0;
        background: #ffffff;
        color: #1f2937;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 14px;
        line-height: 1.45;
    }

    .sheet {
        width: 210mm;
        margin: 0 auto;
        padding: 7mm 13mm 11mm 13mm;
        display: flex;
        flex-direction: column;
    }

    .row {
        margin: 0;
        padding: 0 0 11px 0;
        border-bottom: 1px solid #d1d5db;
    }

    .row + .row {
        margin-top: 11px;
    }

    .name {
        margin: 0;
        font-size: 46px;
        line-height: 1.02;
        color: #111827;
        letter-spacing: -0.02em;
        font-weight: 700;
    }

    .role {
        margin-top: 4px;
        color: #4b5563;
        font-size: 22px;
        font-weight: 500;
    }

    .contacts {
        margin-top: 8px;
        color: #6b7280;
        font-size: 14px;
    }

    .contacts span { white-space: nowrap; }

    .contacts a {
        color: #6b7280;
        text-decoration: none;
    }

    .summary {
        margin-top: 10px;
        color: #374151;
        font-size: 14px;
    }

    .section-title {
        margin: 0 0 8px 0;
        color: #111827;
        font-size: 28px;
        line-height: 1.1;
        font-weight: 700;
        letter-spacing: -0.01em;
    }

    .entry {
        margin: 0 0 12px 0;
    }

    .entry:last-child {
        margin-bottom: 0;
    }

    .entry-title {
        margin: 0;
        color: #111827;
        font-size: 15px;
        font-weight: 700;
    }

    .entry-sub {
        margin-top: 2px;
        color: #374151;
        font-size: 14px;
    }

    .entry-meta {
        margin-top: 2px;
        color: #6b7280;
        font-size: 14px;
    }

    .entry-summary {
        margin-top: 5px;
        color: #4b5563;
    }

    .highlights {
        margin: 5px 0 0 13px;
        padding: 0;
        color: #374151;
    }

    .highlights li {
        margin: 0 0 3px 0;
    }

    .skills-list,
    .simple-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .skills-list li,
    .simple-list li {
        margin: 0 0 5px 0;
        color: #374151;
    }

    .skills-label {
        color: #111827;
        font-weight: 700;
    }

    .rich p { margin: 0 0 7px 0; }
    .rich ul, .rich ol { margin: 7px 0 0 16px; }
    .rich li { margin: 0 0 5px 0; }
	.bg-head .sheet {
		padding-bottom: 0;
		background-color: {{$colorScheme}};
	}
	.head h1 {
		color: {{$dynamicTextColor}};
	}
	.head .role {
		font-weight: bold;
		color: {{$dynamicTextColor}};
	}
	.head .contacts,
	.head .contacts a,
	.head .summary.rich
	{
		color: {{$dynamicTextColor}};
	}
</style>

<div class="head bg-head">
	<div class="sheet">
		<section class="row" style="{{ $sectionOrderStyle('basics') }}">
			<h1 class="name">{{ $safeText(data_get($basics, 'name')) !== '' ? $safeText(data_get($basics, 'name')) : 'Your Name' }}</h1>
			@if($label !== '')
				<div class="role">{{ $label }}</div>
			@endif

			@if($email !== '' || $phone !== '' || $displayLocation !== '' || $url !== '')
				<div class="contacts">
					@if($email !== '')<span>{{ $email }}</span>@endif
					@if($email !== '' && $phone !== '') <span> | </span> @endif
					@if($phone !== '')<span>{{ $phone }}</span>@endif
					@if(($email !== '' || $phone !== '') && $displayLocation !== '') <span> | </span> @endif
					@if($displayLocation !== '')<span>{{ $displayLocation }}</span>@endif
					@if(($email !== '' || $phone !== '' || $displayLocation !== '') && $url !== '') <span> | </span> @endif
					@if($url !== '')<a href="{{ $url }}">{{ $url }}</a>@endif
				</div>
			@endif

			@if(!empty($basics['profiles']) && is_array($basics['profiles']))
				<div class="contacts" style="margin-top: 4px;">
					@foreach($basics['profiles'] as $index => $profile)
						@php
							$profileUrl = $safeText(data_get($profile, 'url')) ?: $safeText(data_get($profile, 'link'));
							$profileName = $safeText(data_get($profile, 'network')) ?: $safeText(data_get($profile, 'username')) ?: $safeText(data_get($profile, 'name'));
						@endphp
						@if($profileUrl !== '' || $profileName !== '')
							@if($index > 0) <span> | </span> @endif
							<span>
								@if($profileName !== ''){{ $profileName }}@endif
								@if($profileUrl !== '')
									@if($profileName !== ''): @endif<a href="{{ $profileUrl }}">{{ $profileUrl }}</a>
								@endif
							</span>
						@endif
					@endforeach
				</div>
			@endif

			@if(!empty($basics['summary']))
				<div class="summary rich">{!! $basics['summary'] !!}</div>
			@endif
		</div>
    </section>
</div>
<div class="sheet">
    @if(!empty($workItems))
        <section class="row" style="{{ $sectionOrderStyle('work') }}">
            <h2 class="section-title">Work Experience</h2>
            @foreach($workItems as $work)
                @if(is_array($work))
                    @php
                        $position = $safeText(data_get($work, 'position'));
                        $company = $safeText(data_get($work, 'name'));
                        $range = $fmtDateRange(data_get($work, 'startDate'), data_get($work, 'endDate'));
                        $wcity = $safeText(data_get($work, 'location.city'));
                        $wregion = $safeText(data_get($work, 'location.region'));
                        $wloc = $compactLocation($wcity, $wregion);
                        if ($wloc === '') {
                            $wloc = $safeText(data_get($work, 'location')) ?: $safeText(data_get($work, 'locationName'));
                        }
                        $workSummary = (string) data_get($work, 'summary', '');
                        $highs = (array) data_get($work, 'highlights', []);
                    @endphp

                    @if($position !== '' || $company !== '' || $range !== '' || $wloc !== '' || trim(strip_tags($workSummary)) !== '' || !empty($highs))
                        <article class="entry">
                            <p class="entry-title">{{ $company !== '' ? $company : ($position !== '' ? $position : 'Role') }}</p>
                            @if($position !== '' && $company !== '')
                                <div class="entry-sub">{{ $position }}</div>
                            @endif
                            @if($range !== '' || $wloc !== '')
                                <div class="entry-meta">
                                    @if($range !== ''){{ $range }}@endif
                                    @if($range !== '' && $wloc !== '') | @endif
                                    @if($wloc !== ''){{ $wloc }}@endif
                                </div>
                            @endif

                            @if(trim(strip_tags($workSummary)) !== '')
                                <div class="entry-summary rich">{!! $workSummary !!}</div>
                            @endif

                            @if(!empty($highs))
                                <ul class="highlights">
                                    @foreach($highs as $h)
                                        @if(is_string($h) && trim($h) !== '')
                                            <li>{{ $h }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endif
                        </article>
                    @endif
                @endif
            @endforeach
        </section>
    @endif

    @if(!empty(data_get($resume, 'skills.body')) || $hasLanguages)
        <section class="row" style="{{ $sectionOrderStyle('skills') }}">
            <h2 class="section-title">Skills</h2>

            @if(!empty(data_get($resume, 'skills.body')))
                <div class="rich">{!! data_get($resume, 'skills.body') !!}</div>
            @endif

            @if($hasLanguages)
                <ul class="skills-list" style="margin-top: 6px;">
                    @foreach($sidebarLanguages as $lang)
                        @php $l = $normalizeLanguage($lang); @endphp
                        @if($l['name'] !== '')
                            <li><span class="skills-label">{{ $l['name'] }}</span>@if($l['meta'] !== ''): {{ $l['meta'] }}@endif</li>
                        @endif
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

    @if(!empty($eduItems) || !empty($certItems) || $hasCertificate)
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
                    @endphp
                    @if($institution !== '' || $studyType !== '' || $area !== '' || $score !== '' || $range !== '')
                        <article class="entry">
                            <p class="entry-title">{{ $institution !== '' ? $institution : 'Education' }}</p>
                            @if($studyType !== '' || $area !== '')
                                <div class="entry-sub">{{ trim($studyType . ($studyType !== '' && $area !== '' ? ' of ' : '') . $area) }}</div>
                            @endif
                            @if($range !== '' || $score !== '')
                                <div class="entry-meta">
                                    @if($range !== ''){{ $range }}@endif
                                    @if($range !== '' && $score !== '') | @endif
                                    @if($score !== '')GPA: {{ $score }}@endif
                                </div>
                            @endif
                        </article>
                    @endif
                @endif
            @endforeach

            @if(!empty($certItems))
                @foreach($certItems as $cert)
                    @if(is_array($cert))
                        @php
                            $cname  = $safeText(data_get($cert, 'name'));
                            $issuer = $safeText(data_get($cert, 'issuer'));
                            $date   = $safeText(data_get($cert, 'date'));
                        @endphp
                        @if($cname !== '' || $issuer !== '' || $date !== '')
                            <article class="entry">
                                <p class="entry-title">{{ $cname !== '' ? $cname : 'Certificate' }}</p>
                                @if($issuer !== '')<div class="entry-sub">{{ $issuer }}</div>@endif
                                @if($date !== '')<div class="entry-meta">{{ $date }}</div>@endif
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

    @if($hasWebsites)
        <section class="row" style="{{ $sectionOrderStyle('for_us_candidates') }}">
            <h2 class="section-title">Websites</h2>
            <ul class="simple-list">
                @foreach($websites as $site)
                    @php $w = $normalizeWebsite($site); @endphp
                    @if($w['url'] !== '')
                        <li><span class="skills-label">{{ $w['label'] }}:</span> <a href="{{ $w['url'] }}" style="color:#4b5563; text-decoration:none;">{{ $w['url'] }}</a></li>
                    @endif
                @endforeach
            </ul>
        </section>
    @endif

    <section class="row" style="border-bottom: 0; padding-bottom: 0; {{ $sectionOrderStyle('references') }}">
        <h2 class="section-title">References</h2>
        @if(!empty($refItems))
            @foreach($refItems as $r)
                @if(is_array($r))
                    @php
                        $refName = $safeText(data_get($r, 'name'));
                        $refBody = (string) data_get($r, 'reference', '');
                        $refTitle = $safeText(data_get($r, 'title')) ?: $safeText(data_get($r, 'position'));
                        $refCompany = $safeText(data_get($r, 'company')) ?: $safeText(data_get($r, 'organization'));
                    @endphp
                    @if($refName !== '' || trim(strip_tags($refBody)) !== '' || $refTitle !== '' || $refCompany !== '')
                        <article class="entry">
                            <p class="entry-title">{{ $refName !== '' ? $refName : 'Reference' }}</p>
                            @if($refTitle !== '' || $refCompany !== '')
                                <div class="entry-sub">{{ trim($refTitle . ($refTitle !== '' && $refCompany !== '' ? ', ' : '') . $refCompany) }}</div>
                            @endif
                            @if(trim(strip_tags($refBody)) !== '')
                                <div class="entry-summary rich">{!! $refBody !!}</div>
                            @endif
                        </article>
                    @endif
                @endif
            @endforeach
        @else
            <div class="entry-meta">Available upon request.</div>
        @endif
    </section>
</div>
