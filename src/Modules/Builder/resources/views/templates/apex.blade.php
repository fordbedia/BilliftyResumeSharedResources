{{-- shared-resources/src/Modules/Builder/resources/views/templates/apex.blade.php --}}

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
        if (strpos($l, 'expert') !== false) return 100;
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
    if (empty($refItems)) {
        $refItems = (array) data_get($resume, 'reference', []);
    }
?>

<style>
    @page { margin: 0; }
    * { box-sizing: border-box; }

    body {
        margin: 0;
        padding: 0;
        background: #1f2329;
        color: #344251;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 14px;
        line-height: 1.45;
    }

    .page {
        width: 210mm;
        margin: 0 auto;
        padding: 7mm;
        background: linear-gradient(180deg, #1f2329 0%, #14181d 100%);
    }

    .sheet {
        width: 100%;
        background: #edf1f4;
        border-radius: 10px;
        border: 1px solid #c6d0d7;
        overflow: hidden;
    }

    .top-accent {
        height: 4px;
        background: linear-gradient(90deg, #22d3be 0%, #14b8a6 100%);
    }

    .inner {
        padding: 7mm 8mm 8mm 8mm;
        background: #edf1f4;
        display: flex;
        flex-direction: column;
    }

    .hero {
        display: flex;
        gap: 10px;
        justify-content: space-between;
        align-items: flex-start;
    }

    .hero-main {
        flex: 1;
        min-width: 0;
    }

    .identity {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .avatar {
        width: 100px;
        height: 100px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #c9d2d9;
        background: #d9e0e6;
    }

    .avatar-fallback {
        width: 54px;
        height: 54px;
        border-radius: 8px;
        border: 1px solid #c9d2d9;
        background: #d9e0e6;
        color: #1f2937;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        font-weight: 700;
    }

    .name {
        margin: 0;
        color: #1e293b;
        font-size: 40px;
        line-height: .94;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .role {
        margin-top: 5px;
        color: #0f766e;
        font-size: 20px;
        font-weight: 700;
    }

    .summary {
        margin-top: 9px;
        color: #4b5563;
        font-size: 14px;
        line-height: 1.5;
        max-width: 95%;
    }

    .contact-card {
        width: 39%;
        min-width: 200px;
        background: #f4f7f9;
        border: 1px solid #d6dee4;
        border-radius: 8px;
        padding: 8px 10px;
        align-self: flex-start;
    }

    .contact-row {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #4b5563;
        font-size: 14px;
        margin: 0 0 7px 0;
        word-break: break-word;
    }

    .contact-row:last-child { margin-bottom: 0; }

    .contact-icn {
        width: 17px;
        height: 17px;
        border-radius: 999px;
        background: #d1fae5;
        border: 1px solid #a7f3d0;
        color: #0f766e;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex: 0 0 17px;
    }

    .contact-row a {
        color: #4b5563;
        text-decoration: none;
    }

    .section-block { margin-top: 12px; }
    .section-block:first-of-type { margin-top: 14px; }

    .section-title {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .section-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: #ccfbf1;
        color: #0f766e;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        border: 1px solid #99f6e4;
        flex: 0 0 30px;
    }

    .section-title h2 {
        margin: 0;
        color: #1f2937;
        font-size: 38px;
        line-height: 1;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .section-divider {
        margin-top: 6px;
        border-top: 1px solid #d8e0e6;
    }

    .timeline {
        position: relative;
        margin-top: 8px;
    }

    .timeline::before {
        content: "";
        position: absolute;
        left: 4px;
        top: 2px;
        bottom: 2px;
        width: 1px;
        background: #d4dce2;
    }

    .timeline-item {
        position: relative;
        padding-left: 18px;
        margin-bottom: 11px;
    }

    .timeline-item:last-child { margin-bottom: 0; }

    .dot {
        position: absolute;
        left: 0;
        top: 2px;
        width: 9px;
        height: 9px;
        border-radius: 999px;
        border: 1px solid #c6d0d7;
        background: #d1d5db;
    }

    .dot.active {
        background: #14b8a6;
        border-color: #0f766e;
    }

    .entry-head {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 10px;
    }

    .entry-title {
        margin: 0;
        color: #111827;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.25;
    }

    .entry-right {
        color: #6b7280;
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
        text-align: right;
    }

    .entry-sub {
        margin-top: 2px;
        color: #6b7280;
        font-size: 14px;
        font-weight: 600;
    }

    .entry-copy {
        margin-top: 4px;
        color: #4b5563;
        font-size: 14px;
        line-height: 1.45;
    }

    .bullets {
        margin: 4px 0 0 0;
        padding: 0;
        list-style: none;
        color: #4b5563;
        font-size: 14px;
    }

    .bullets li {
        margin: 0 0 2px 0;
        padding-left: 11px;
        position: relative;
        line-height: 1.4;
    }

    .bullets li::before {
        content: "\2713";
        position: absolute;
        left: 0;
        top: 0;
        color: #14b8a6;
        font-size: 14px;
        font-weight: 700;
    }

    .skills-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 9px;
        margin-top: 10px;
    }

    .skill-card {
        background: #e5eaee;
        border: 1px solid #d5dde3;
        border-radius: 8px;
        padding: 8px;
    }

    .skill-head {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #1f2937;
        font-size: 14px;
        font-weight: 700;
    }

    .skill-head-dot {
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: #14b8a6;
        flex: 0 0 7px;
    }

    .skill-tags {
        margin-top: 6px;
        color: #4b5563;
        font-size: 14px;
        line-height: 1.45;
    }

    .skill-tag {
        display: inline-block;
        margin: 0 10px 4px 0;
        white-space: nowrap;
    }

    .skills-body {
        margin-top: 10px;
        background: #e5eaee;
        border: 1px solid #d5dde3;
        border-radius: 8px;
        padding: 8px;
        color: #4b5563;
        font-size: 14px;
    }

    .bottom-grid {
        margin-top: 12px;
        display: block;
    }

    .bottom-grid > .section-block {
        margin-top: 12px;
    }

    .bottom-grid > .section-block:first-child {
        margin-top: 0;
    }

    .edu-item {
        position: relative;
        padding-left: 14px;
        margin-bottom: 9px;
    }

    .edu-item:last-child { margin-bottom: 0; }

    .edu-item::before {
        content: "";
        position: absolute;
        left: 0;
        top: 3px;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #d1d5db;
        border: 1px solid #c6d0d7;
    }

    .edu-title {
        margin: 0;
        color: #1f2937;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.25;
    }

    .edu-sub {
        margin-top: 1px;
        color: #0f766e;
        font-size: 14px;
        font-weight: 700;
    }

    .edu-meta {
        margin-top: 1px;
        color: #6b7280;
        font-size: 14px;
    }

    .ref-card {
        background: transparent;
        border: 0;
        border-radius: 0;
        padding: 0;
        margin-bottom: 9px;
    }

    .ref-card:last-child { margin-bottom: 0; }

    .ref-quote {
        margin: 0;
        color: #4b5563;
        font-size: 14px;
        line-height: 1.45;
    }

    .ref-person {
        margin-top: 7px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .ref-avatar {
        width: 20px;
        height: 20px;
        border-radius: 999px;
        background: #d1d5db;
        color: #4b5563;
        font-size: 14px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 20px;
    }

    .ref-name {
        color: #1f2937;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.2;
    }

    .ref-meta {
        color: #6b7280;
        font-size: 14px;
        line-height: 1.2;
    }

    .rich p { margin: 0 0 4px 0; }
    .rich ul, .rich ol {
        margin: 4px 0 0 0;
        padding: 0;
        list-style: none;
    }
    .rich li {
        margin: 0 0 2px 0;
        padding-left: 11px;
        position: relative;
        line-height: 1.42;
    }
    .rich li::before {
        content: "\2713";
        position: absolute;
        left: 0;
        top: 0;
        color: #14b8a6;
        font-size: 14px;
        font-weight: 700;
    }
</style>

<div class="page">
    <div class="sheet">
        <div class="top-accent"></div>
        <div class="inner">
            <header class="hero" style="{{ $sectionOrderStyle('basics') }}">
                <div class="hero-main">
                    <div class="identity">
                        @if($photo !== '')
                            <img class="avatar" src="{{ $photo }}" alt="Profile Photo" />
                        @else
                            <span class="avatar-fallback">{{ $toInitials($safeText(data_get($basics, 'name')) !== '' ? $safeText(data_get($basics, 'name')) : 'Your Name') }}</span>
                        @endif
                        <div>
                            <h1 class="name">{{ $safeText(data_get($basics, 'name')) !== '' ? $safeText(data_get($basics, 'name')) : 'Your Name' }}</h1>
                            @if($label !== '')
                                <div class="role">{{ $label }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                @if(
                    $email !== '' ||
                    $phone !== '' ||
                    $displayLocation !== '' ||
                    $url !== '' ||
                    (!empty($basics['profiles']) && is_array($basics['profiles'])) ||
                    $hasWebsites
                )
                    <aside class="contact-card">
                        @if($email !== '')
                            <div class="contact-row"><span class="contact-icn">&#9993;</span><span>{{ $email }}</span></div>
                        @endif
                        @if($phone !== '')
                            <div class="contact-row"><span class="contact-icn">&#9990;</span><span>{{ $phone }}</span></div>
                        @endif
                        @if($displayLocation !== '')
                            <div class="contact-row"><span class="contact-icn">&#9679;</span><span>{{ $displayLocation }}</span></div>
                        @endif
                        @if($url !== '')
                            <div class="contact-row"><span class="contact-icn">&#128279;</span><span><a href="{{ $url }}">{{ preg_replace('#^https?://#', '', $url) }}</a></span></div>
                        @endif

                        @if(!empty($basics['profiles']) && is_array($basics['profiles']))
                            @foreach($basics['profiles'] as $profile)
                                @php
                                    $profileUrl = $safeText(data_get($profile, 'url')) ?: $safeText(data_get($profile, 'link'));
                                    $profileNetwork = $safeText(data_get($profile, 'network'));
                                    $profileUser = $safeText(data_get($profile, 'username'));
                                    $profileLabel = $profileUrl !== '' ? preg_replace('#^https?://#', '', $profileUrl) : '';
                                    if ($profileUser !== '' && strpos($profileUser, '@') !== 0 && strtolower($profileNetwork) !== 'linkedin') {
                                        $profileUser = '@' . $profileUser;
                                    }
                                    if ($profileUser !== '') {
                                        $profileLabel = $profileUser;
                                    }
                                @endphp
                                @if($profileUrl !== '')
                                    <div class="contact-row"><span class="contact-icn">&#9679;</span><span><a href="{{ $profileUrl }}">{{ $profileLabel }}</a></span></div>
                                @endif
                            @endforeach
                        @endif

                        @if($hasWebsites)
                            @foreach($websites as $site)
                                @php $w = $normalizeWebsite($site); @endphp
                                @if($w['url'] !== '')
                                    <div class="contact-row"><span class="contact-icn">&#9679;</span><span><a href="{{ $w['url'] }}">{{ preg_replace('#^https?://#', '', $w['url']) }}</a></span></div>
                                @endif
                            @endforeach
                        @endif
                    </aside>
                @endif
            </header>

			@if(!empty($basics['summary']))
				<div class="summary rich">{!! $basics['summary'] !!}</div>
			@endif

            @if(!empty($workItems))
                <section class="section-block" style="{{ $sectionOrderStyle('work') }}">
                    <div class="section-title">
                        <span class="section-icon">&#128188;</span>
                        <h2>Professional Experience</h2>
                    </div>
                    <div class="section-divider"></div>

                    <div class="timeline">
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
                                    <article class="timeline-item">
                                        <span class="dot {{ $loop->first ? 'active' : '' }}"></span>
                                        <div class="entry-head">
                                            <p class="entry-title">{{ $position !== '' ? $position : ($company !== '' ? $company : 'Role') }}</p>
                                            @if($range !== '')
                                                <div class="entry-right">{{ $range }}</div>
                                            @endif
                                        </div>
                                        <div class="entry-sub">
                                            {{ $company !== '' ? $company : '' }}
                                            @if($company !== '' && $wloc !== '') | @endif
                                            {{ $wloc !== '' ? $wloc : '' }}
                                        </div>
                                        @if(trim(strip_tags($workSummary)) !== '')
                                            <div class="entry-copy rich">{!! $workSummary !!}</div>
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
                    </div>
                </section>
            @endif

            @php
                $rawSkills = data_get($resume, 'skills', []);
                $skillRows = (array) data_get($resume, 'skills.skills', []);
                $skillsBody = (string) data_get($resume, 'skills.body', '');

                if ($skillsBody === '') {
                    $skillsBody = (string) data_get($resume, 'skills.0.body', '');
                }

                if ($skillsBody === '' && is_string($rawSkills)) {
                    $skillsBody = trim($rawSkills);
                }

                if (empty($skillRows) && is_iterable($rawSkills)) {
                    foreach ($rawSkills as $item) {
                        if (is_string($item) && trim($item) !== '') {
                            $skillRows[] = ['name' => trim($item)];
                            continue;
                        }
                        if (!is_array($item) && !is_object($item)) {
                            continue;
                        }
                        $candidateName = $safeText(data_get($item, 'name'));
                        $candidateLevel = $safeText(data_get($item, 'level'));
                        $candidateKeywords = (array) data_get($item, 'keywords', []);
                        if ($candidateName !== '' || $candidateLevel !== '' || !empty($candidateKeywords)) {
                            $skillRows[] = $item;
                        }
                    }
                }

                $languageTags = [];
                if ($hasLanguages) {
                    foreach ($sidebarLanguages as $lang) {
                        $l = $normalizeLanguage($lang);
                        if ($l['name'] !== '') {
                            $languageTags[] = $l['name'] . ($l['meta'] !== '' ? ' (' . $l['meta'] . ')' : '');
                        }
                    }
                }
            @endphp
            @if($skillsBody !== '' || !empty($skillRows) || !empty($languageTags))
                <section class="section-block" style="{{ $sectionOrderStyle('skills') }}">
                    <div class="section-title">
                        <span class="section-icon">&#60;/&#62;</span>
                        <h2>Skills</h2>
                    </div>
                    <div class="section-divider"></div>

                    @if($skillsBody !== '')
                        <div class="skills-body rich">{!! $skillsBody !!}</div>
                    @endif

                    @if(!empty($skillRows) || !empty($languageTags))
                        <div class="skills-grid">
                            @foreach($skillRows as $skill)
                                @if(is_array($skill) || is_object($skill))
                                    @php
                                        $skillName = $safeText(data_get($skill, 'name'));
                                        $skillLevel = $safeText(data_get($skill, 'level'));
                                        $skillKeywords = (array) data_get($skill, 'keywords', []);
                                        $skillKeywordsClean = [];
                                        foreach ($skillKeywords as $kw) {
                                            if (is_string($kw) && trim($kw) !== '') {
                                                $skillKeywordsClean[] = trim($kw);
                                            }
                                        }
                                        if (empty($skillKeywordsClean) && $skillLevel !== '') {
                                            $skillKeywordsClean[] = $skillLevel;
                                        }
                                    @endphp
                                    @if($skillName !== '' && !empty($skillKeywordsClean))
                                        <article class="skill-card">
                                            <div class="skill-head"><span class="skill-head-dot"></span><span>{{ $skillName }}</span></div>
                                            <div class="skill-tags">
                                                @foreach($skillKeywordsClean as $tag)
                                                    <span class="skill-tag">{{ $tag }}</span>
                                                @endforeach
                                            </div>
                                        </article>
                                    @endif
                                @elseif(is_string($skill) && trim($skill) !== '')
                                    <article class="skill-card">
                                        <div class="skill-head"><span class="skill-head-dot"></span><span>{{ trim($skill) }}</span></div>
                                    </article>
                                @endif
                            @endforeach

                            @if(!empty($languageTags))
                                <article class="skill-card">
                                    <div class="skill-head"><span class="skill-head-dot"></span><span>Languages</span></div>
                                    <div class="skill-tags">
                                        @foreach($languageTags as $tag)
                                            <span class="skill-tag">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                </article>
                            @endif
                        </div>
                    @endif
                </section>
            @endif

            <div class="bottom-grid">
                @if(!empty($eduItems) || !empty($certItems) || $hasCertificate)
                    <section class="section-block" style="{{ $sectionOrderStyle('education') }}">
                        <div class="section-title">
                            <span class="section-icon">&#127891;</span>
                            <h2>Education</h2>
                        </div>
                        <div class="section-divider"></div>

                        <div style="margin-top: 8px;">
                            @foreach($eduItems as $edu)
                                @if(is_array($edu))
                                    @php
                                        $institution = $safeText(data_get($edu, 'institution'));
                                        $studyType   = $safeText(data_get($edu, 'studyType'));
                                        $area        = $safeText(data_get($edu, 'area'));
                                        $score       = $safeText(data_get($edu, 'score'));
                                        $range       = $fmtDateRange(data_get($edu, 'startDate'), data_get($edu, 'endDate'));
                                        $eduHighlights = (array) data_get($edu, 'highlights', []);
                                        $eduHighlightsClean = [];
                                        foreach ($eduHighlights as $eh) {
                                            if (is_string($eh) && trim($eh) !== '') {
                                                $eduHighlightsClean[] = trim($eh);
                                            }
                                        }
                                        $eduHighlightsText = implode(' | ', $eduHighlightsClean);
                                    @endphp
                                    @if($institution !== '' || $studyType !== '' || $area !== '' || $score !== '' || $range !== '' || $eduHighlightsText !== '')
                                        <article class="edu-item">
                                            <p class="edu-title">{{ $institution !== '' ? $institution : 'Education' }}</p>
                                            @if($studyType !== '' || $area !== '')
                                                <div class="edu-sub">{{ trim($studyType . ($studyType !== '' && $area !== '' ? ' in ' : '') . $area) }}</div>
                                            @endif
                                            @if($range !== '' || $score !== '' || $eduHighlightsText !== '')
                                                <div class="edu-meta">
                                                    @if($range !== ''){{ $range }}@endif
                                                    @if($range !== '' && $score !== '') | @endif
                                                    @if($score !== '')GPA: {{ $score }}@endif
                                                    @if(($range !== '' || $score !== '') && $eduHighlightsText !== '') | @endif
                                                    @if($eduHighlightsText !== ''){{ $eduHighlightsText }}@endif
                                                </div>
                                            @endif
                                        </article>
                                    @endif
                                @endif
                            @endforeach

                            @foreach($certItems as $cert)
                                @if(is_array($cert))
                                    @php
                                        $cname  = $safeText(data_get($cert, 'name'));
                                        $issuer = $safeText(data_get($cert, 'issuer'));
                                        $date   = $safeText(data_get($cert, 'date'));
                                    @endphp
                                    @if($cname !== '' || $issuer !== '' || $date !== '')
                                        <article class="edu-item">
                                            <p class="edu-title">{{ $cname !== '' ? $cname : 'Certificate' }}</p>
                                            @if($issuer !== '')<div class="edu-sub">{{ $issuer }}</div>@endif
                                            @if($date !== '')<div class="edu-meta">{{ $date }}</div>@endif
                                        </article>
                                    @endif
                                @endif
                            @endforeach

                            @if(empty($eduItems) && empty($certItems) && $hasCertificate)
                                <div class="skills-body rich">{!! $certificateBody !!}</div>
                            @endif
                        </div>
                    </section>
                @endif

                <section class="section-block" style="{{ $sectionOrderStyle('references') }}">
                    <div class="section-title">
                        <span class="section-icon">&#10077;</span>
                        <h2>References</h2>
                    </div>
                    <div class="section-divider"></div>

                    <div style="margin-top: 8px;">
                        @if(isset($resume['references']['reference']) && $resume['references']['reference'] !== '')
                            <article class="ref-card">
                                <div class="ref-quote rich">{!! $resume['references']['reference'] !!}</div>
                            </article>
                        @elseif(isset($resume['reference']['reference']) && $resume['reference']['reference'] !== '')
                            <article class="ref-card">
                                <div class="ref-quote rich">{!! $resume['reference']['reference'] !!}</div>
                            </article>
                        @endif

                        @if(!empty($refItems))
                            @php $hasRenderedRef = false; @endphp
                            @foreach($refItems as $r)
                                @php
                                    $refName = '';
                                    $refTitle = '';
                                    $refCompany = '';
                                    $refEmail = '';
                                    $refPhone = '';
                                    $refBody = '';

                                    if (is_array($r) || is_object($r)) {
                                        $refName = $safeText(data_get($r, 'name'));
                                        $refTitle = $safeText(data_get($r, 'title')) ?: $safeText(data_get($r, 'position'));
                                        $refCompany = $safeText(data_get($r, 'company')) ?: $safeText(data_get($r, 'organization'));
                                        $refEmail = $safeText(data_get($r, 'email'));
                                        $refPhone = $safeText(data_get($r, 'phone'));
                                        $refBody = (string) data_get($r, 'reference', '');
                                    } elseif (is_string($r)) {
                                        $refBody = $r;
                                    }
                                @endphp
                                    <article class="ref-card">
                                        @if($refBody)
                                            <p class="ref-quote">{!! $refBody !!}</p>
                                        @endif
                                    </article>
                            @endforeach
                        @endif
                    </div>
                </section>
            </div>

            @if($hasProject)
                <section class="section-block" style="{{ $sectionOrderStyle('for_us_candidates') }}">
                    <div class="section-title"><span class="section-icon">&#9679;</span><h2>Selected Projects</h2></div>
                    <div class="section-divider"></div>
                    <div class="entry-copy rich" style="margin-top: 8px;">{!! $projectBody !!}</div>
                </section>
            @endif

            @if($hasAccomplishment)
                <section class="section-block" style="{{ $sectionOrderStyle('additional_information') }}">
                    <div class="section-title"><span class="section-icon">&#9679;</span><h2>Accomplishments</h2></div>
                    <div class="section-divider"></div>
                    <div class="entry-copy rich" style="margin-top: 8px;">{!! $accomplishmentBody !!}</div>
                </section>
            @endif

            @if($hasVolunteer)
                <section class="section-block" style="{{ $sectionOrderStyle('for_us_candidates') }}">
                    <div class="section-title"><span class="section-icon">&#9679;</span><h2>Volunteer</h2></div>
                    <div class="section-divider"></div>
                    <div class="entry-copy rich" style="margin-top: 8px;">{!! $volunteerBody !!}</div>
                </section>
            @endif

            @if($hasAffiliation)
                <section class="section-block" style="{{ $sectionOrderStyle('for_us_candidates') }}">
                    <div class="section-title"><span class="section-icon">&#9679;</span><h2>Affiliations</h2></div>
                    <div class="section-divider"></div>
                    <div class="entry-copy rich" style="margin-top: 8px;">{!! $affiliationBody !!}</div>
                </section>
            @endif

            @if($hasInterest)
                <section class="section-block" style="{{ $sectionOrderStyle('for_us_candidates') }}">
                    <div class="section-title"><span class="section-icon">&#9679;</span><h2>Interests</h2></div>
                    <div class="section-divider"></div>
                    <div class="entry-copy rich" style="margin-top: 8px;">{!! $interestBody !!}</div>
                </section>
            @endif
        </div>
    </div>
</div>
