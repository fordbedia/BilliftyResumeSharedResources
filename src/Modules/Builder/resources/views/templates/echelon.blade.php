{{-- shared-resources/src/Modules/Builder/resources/views/templates/echelon.blade.php --}}

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
    /* =========================
       FULL BLEED (NO PAGE MARGINS) + KEEP SCROLL
       ========================= */
    @page { margin: 0; }

    html, body{
        margin: 0;
        padding: 0;
        width: 100%;
        height: auto;
        overflow: visible;
    }

    * { box-sizing: border-box; }

    :root{
        --ink: #0F172A;
        --muted: #94A3B8;
        --muted2: #64748B;
        --panel: #0B1220;
        --primary: {{ $primaryColor }};
        --gradient-top: {{ $gradientTop }};
        --gradient-bottom: {{ $gradientBottom }};
        --primary2: #7C3AED;
        --soft: #F6F8FC;
        --text-color: {{ $dynamicTextColor }};
    }

    body{
        font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, "DejaVu Sans", sans-serif;
        font-size: 14px;
        line-height: 1.45;
        color: var(--ink);
        margin: 0;
        padding: 0;
    }

    .sheet{
        width: 100%;
		height: auto;
        margin: 0;
        border: 0;
        border-radius: 0;
        overflow: visible;
        background: #FFFFFF;
		align-items: stretch;
    }
	.sheet{
	  position: relative; /* for stacking context */
	  width: 100%;
	  height: auto;
	  margin: 0;
	  border: 0;
	  border-radius: 0;
	  overflow: visible;
	  background: #FFFFFF;
	}

	/* THIS is the “full height” sidebar background */
	.sheet:before{
	  content: "";
	  position: fixed;     /* key: fills viewport + repeats per page when printing */
	  top: 0;
	  bottom: 0;
	  left: 0;
	  width: 340px;        /* same as your sidebar column */
	  background: var(--primary);
	  z-index: 0;
	  pointer-events: none;
	}

	/* Ensure content sits above the background strip */
	.layout{
	  position: relative;
	  z-index: 1;
	  display: grid;
	  grid-template-columns: 340px 1fr;
	  min-height: 0;
	  align-items: stretch;
	}

    /* ===== Sidebar ===== */
    /* UPDATED: remove gradient, use solid background color (primary) */
    .side{
        background: {{$colorScheme}};
        color: #FFFFFF; /* base text */
        padding: 26px 22px;
		align-items: stretch;
		height: 100%;
    }

    .avatar{
        width: 120px;
        height: 120px;
        border-radius: 18px;
        overflow: hidden;
        background: rgba(255,255,255,0.10);
        border: 2px solid rgba(255,255,255,0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
    }
    .avatar img{
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
		object-position: center 0;
    }
    .avatar .initials{
        font-size: 24px;
        font-weight: 800;
        letter-spacing: 1px;
        color: {{ $dynamicTextColor ?? '#FFFFFF' }};
    }

    .side-name{
        font-size: 32px;
        line-height: 1.05;
        font-weight: 800;
        margin: 0;
        letter-spacing: -0.3px;
        color: {{ $dynamicTextColor ?? '#FFFFFF' }};
    }

    .side-role{
        margin-top: 6px;
        font-size: 16px;
        font-weight: 500;
        color: {{ $dynamicTextColor ?? '#FFFFFF' }};
        opacity: 0.92;
    }

    .side-summary{
        margin-top: 16px;
        max-width: 260px;
        font-size: 14px;
        color: {{ $dynamicTextColor ?? 'rgba(255,255,255,0.92)' }};
        opacity: 0.92;
    }
    .side-summary p{ margin: 0; }

    .block{
        margin-top: 26px;
    }

    .block-title{
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        letter-spacing: 0.3px;
        font-size: 14px;
        margin-bottom: 10px;
        color: {{ $dynamicTextColor ?? 'rgba(255,255,255,0.90)' }};
    }
    .block-title .barline{
        width: 34px;
        height: 2px;
        border-radius: 999px;
        background: rgba(255,255,255,0.35);
        display: inline-block;
    }

    .divider{
        height: 1px;
        background: rgba(255,255,255,0.14);
        margin: 10px 0 12px;
    }

    .contact-list{
        display: grid;
        gap: 10px;
        margin-top: 10px;
    }

    .contact-item{
        display: grid;
        grid-template-columns: 18px 1fr;
        gap: 10px;
        align-items: start;
        color: {{ $dynamicTextColor ?? 'rgba(255,255,255,0.92)' }};
    }
    .contact-item a{
        text-decoration: none;
        word-break: break-word;
        color: {{ $dynamicTextColor ?? 'rgba(255,255,255,0.95)' }};
    }
    .contact-item .meta{
        color: {{ $dynamicTextColor ?? 'rgba(255,255,255,0.85)' }};
        font-size: 14px;
    }

    /* Skills bars */
    .skills{
        display: grid;
        gap: 12px;
        margin-top: 14px;
    }
    .skill{
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: center;
    }
    .skill .label{
        color: {{ $dynamicTextColor ?? 'rgba(255,255,255,0.95)' }};
        font-weight: 600;
    }
    .skill .level{
        color: {{ $dynamicTextColor ?? 'rgba(255,255,255,0.75)' }};
        font-size: 14px;
    }
    .bar{
        grid-column: 1 / -1;
        height: 5px;
        border-radius: 999px;
        background: rgba(255,255,255,0.22);
        overflow: hidden;
    }

    /* UPDATED: remove gradient; use dynamicTextColor, rest is white */
    .bar > span{
        display: block;
        height: 100%;
        width: 50%;
        background: {{ $dynamicTextColor ?? '#FFFFFF' }};
        border-radius: 999px;
    }

    /* Languages */
    .langs{
        display: grid;
        gap: 10px;
        margin-top: 12px;
    }
    .lang-row{
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        color: {{ $dynamicTextColor ?? 'rgba(255,255,255,0.92)' }};
        align-items: baseline;
    }
    .lang-row .meta{
        color: {{ $dynamicTextColor ?? 'rgba(255,255,255,0.75)' }};
        font-size: 14px;
    }

    /* ===== Main ===== */
    .main{
        padding: 24px 28px;
        background: #FFFFFF;
    }

    .section{
        margin-bottom: 22px;
    }

    .section-header{
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .section-header .icon{
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        background: rgba(99,102,241,0.10);
    }

    .section-header h2{
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.2px;
        color: var(--ink);
    }

    /* Timeline */
    .timeline{
        position: relative;
        padding-left: 22px;
        display: grid;
        gap: 26px;
    }

    .timeline:before{
        content: "";
        position: absolute;
        left: 9px;
        top: 4px;
        bottom: 4px;
        width: 2px;
        background: linear-gradient(180deg, rgba(99,102,241,0.0) 0%, rgba(99,102,241,0.35) 18%, rgba(226,232,240,1) 80%, rgba(226,232,240,1) 100%);
    }

    .titem{
        width: 100%;
        position: relative;
        padding-left: 0;
    }

    .trow{
        display: flex;
        gap: 18px;
        align-items: flex-start;
    }

    .tleft{
        flex: 1 1 auto;
        min-width: 0;
    }

    .tright{
        flex: 0 0 auto;
        white-space: nowrap;
        color: var(--muted2);
        font-weight: 600;
        font-size: 14px;
        margin-top: 2px;
    }

    .work-summary{
        display: block;
        width: 100%;
        max-width: none !important;
        margin-top: 8px;
    }

    .work-summary, .work-summary *{
        max-width: none !important;
        width: auto !important;
    }

    .work-summary p,
    .work-summary div,
    .work-summary ul,
    .work-summary ol{
        width: 100% !important;
        max-width: none !important;
        display: block !important;
    }

    .work-summary table{
        width: 100% !important;
        max-width: none !important;
    }

    .titem:before{
        content: "";
        position: absolute;
        left: -22px;
        top: 4px;
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #CBD5E1;
        border: 3px solid #FFFFFF;
        box-shadow: 0 0 0 2px rgba(203,213,225,0.5);
    }

    .titem.is-primary:before{
        background: var(--primary);
        box-shadow: 0 0 0 2px rgba(99,102,241,0.35);
    }

    .t-role{
        font-size: 15px;
        font-weight: 800;
        color: var(--ink);
        margin-bottom: 2px;
    }

    .t-company{
        color: var(--primary);
        font-weight: 700;
        margin-bottom: 6px;
    }

    .t-loc{
        color: var(--muted2);
        font-size: 14px;
        margin-bottom: 8px;
    }

    .t-range{
        color: var(--muted2);
        font-weight: 600;
        font-size: 14px;
        margin-top: 2px;
        white-space: nowrap;
    }

    .bullets{
        margin: 0;
        padding-left: 18px;
        color: var(--muted2);
    }
    .bullets li{
        margin: 0 0 8px 0;
    }
    .bullets li::marker{
        color: var(--primary);
    }

    .card{
        background: var(--soft);
        border-radius: 14px;
        padding: 16px;
        border: 1px solid #EEF2F8;
    }

    .edu-stack{
        display: grid;
        gap: 14px;
    }

    .card-row{
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 18px;
        align-items: start;
    }

    .card .title{
        font-size: 14px;
        font-weight: 800;
        margin: 0;
        color: var(--ink);
    }

    .card .linkish{
        color: var(--primary);
        font-weight: 700;
        margin-top: 4px;
    }

    .card .meta{
        margin-top: 6px;
        color: var(--muted2);
        font-size: 14px;
    }

    .refs{
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .ref-name{
        font-weight: 800;
        font-size: 14px;
        margin: 0;
    }

    .ref-sub{
        color: var(--primary);
        font-weight: 700;
        margin-top: 4px;
        font-size: 14px;
    }

    .ref-lines{
        margin-top: 10px;
        display: grid;
        gap: 8px;
        color: var(--muted2);
        font-size: 14px;
    }

    .ref-line{
        display: grid;
        grid-template-columns: 16px 1fr;
        gap: 8px;
        align-items: center;
    }

    .rich p { margin: 0 0 8px 0; }
    .rich ul { margin: 8px 0 0 18px; }
    .rich li { margin: 0 0 6px 0; }

    .svg{ width: 16px; height: 16px; display: inline-block; }
</style>

<div class="sheet">
    <div class="layout">

        {{-- ===================== SIDEBAR ===================== --}}
        <aside class="side">
			@if($photo !== '')
				<div class="avatar">
					<img src="{{ $photo }}" alt="Photo">
				</div>
			@endif

            <h1 class="side-name">{{ $basics['name'] ?? 'Your Name' }}</h1>
            <div class="side-role">{{ $label !== '' ? $label : 'Your Title' }}</div>

            @if(!empty($basics['summary']))
                <div class="side-summary rich">{!! $basics['summary'] !!}</div>
            @else
                <div class="side-summary">Passionate professional crafting user-centered experiences and solutions.</div>
            @endif

            {{-- Contact --}}
            <div class="block">
                <div class="block-title"><span class="barline"></span>Contact</div>
                <div class="divider"></div>

                <div class="contact-list">
                    @if($email !== '')
                        <div class="contact-item">
                            <span>
                                <svg class="svg" viewBox="0 0 24 24" fill="none" style="color: {{$dynamicTextColor ?? 'rgba(255,255,255,0.95)'}};">
                                    <path d="M4 6h16v12H4V6Z" stroke="currentColor" stroke-width="1.8" />
                                    <path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.8" />
                                </svg>
                            </span>
                            <div class="meta"><a href="mailto:{{ $email }}">{{ $email }}</a></div>
                        </div>
                    @endif

                    @if($phone !== '')
                        <div class="contact-item">
                            <span>
                                <svg class="svg" viewBox="0 0 24 24" fill="none" style="color: {{$dynamicTextColor ?? 'rgba(255,255,255,0.95)'}};">
                                    <path d="M7 4h3l1 5-2 1c1 3 3 5 6 6l1-2 5 1v3c0 1-1 2-2 2-8 0-15-7-15-15 0-1 1-2 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <div class="meta">{{ $phone }}</div>
                        </div>
                    @endif

                    @if($displayLocation !== '')
                        <div class="contact-item">
                            <span>
                                <svg class="svg" viewBox="0 0 24 24" fill="none" style="color: {{$dynamicTextColor ?? 'rgba(255,255,255,0.95)'}};">
                                    <path d="M12 21s7-6 7-12a7 7 0 1 0-14 0c0 6 7 12 7 12Z" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M12 11.5A2.5 2.5 0 1 0 12 6.5a2.5 2.5 0 0 0 0 5Z" stroke="currentColor" stroke-width="1.8"/>
                                </svg>
                            </span>
                            <div class="meta">{{ $displayLocation }}</div>
                        </div>
                    @endif

                    @if($url !== '')
                        <div class="contact-item">
                            <span>
                                <svg class="svg" viewBox="0 0 24 24" fill="none" style="color: {{$dynamicTextColor ?? 'rgba(255,255,255,0.95)'}};">
                                    <path d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M2 12h20" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M12 2c3.5 3 3.5 17 0 20" stroke="currentColor" stroke-width="1.8"/>
                                </svg>
                            </span>
                            <div class="meta"><a href="{{ $url }}">{{ $url }}</a></div>
                        </div>
                    @endif

                    @if(!empty($basics['profiles']) && is_array($basics['profiles']))
                        @foreach($basics['profiles'] as $profile)
                            @php
                                $network = $safeText(data_get($profile, 'network'));
                                $purl    = $safeText(data_get($profile, 'url')) ?: $safeText(data_get($profile, 'username'));
                            @endphp
                            @if($purl !== '')
                                <div class="contact-item">
                                    <span>
                                        <svg class="svg" viewBox="0 0 24 24" fill="none" style="color: {{$dynamicTextColor ?? 'rgba(255,255,255,0.95)'}};">
                                            <path d="M4 4h16v16H4V4Z" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M8 11v7" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M8 8.5v.5" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M12 18v-4c0-2 3-2 3 0v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <div class="meta">
                                        <a href="{{ $purl }}">{{ $network !== '' ? $network : 'Profile' }}: {{ $purl }}</a>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Skills --}}
            @if(!empty($resume['skills']) && is_array($resume['skills']))
                <div class="block">
                    <div class="block-title"><span class="barline"></span>Skills</div>
                    <div class="divider"></div>

                    <div class="skills">
                        @foreach($resume['skills'] as $skill)
                            @php
                                $skillName  = '';
                                $skillLevel = '';

                                if (is_array($skill)) {
                                    $skillName  = $safeText(data_get($skill, 'name'));
                                    $skillLevel = $safeText(data_get($skill, 'level'));
                                } else {
                                    $skillName = $safeText($skill);
                                }

                                $pct = $skillPercent($skillLevel);
                            @endphp

                            @if($skillName !== '')
                                <div>
                                    <div class="skill">
                                        <div class="label">{{ $skillName }}</div>
                                        <div class="level">{{ $skillLevel !== '' ? $skillLevel : '' }}</div>
                                        <div class="bar"><span style="width: {{ $pct }}%;"></span></div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Languages --}}
            @if($hasLanguages)
                <div class="block">
                    <div class="block-title"><span class="barline"></span>Languages</div>
                    <div class="divider"></div>

                    <div class="langs">
                        @foreach($sidebarLanguages as $lang)
                            @php $l = $normalizeLanguage($lang); @endphp
                            @if($l['name'] !== '')
                                <div class="lang-row">
                                    <div>{{ $l['name'] }}</div>
                                    <div class="meta">{{ $l['meta'] }}</div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Websites --}}
            @if($hasWebsites)
                <div class="block">
                    <div class="block-title"><span class="barline"></span>Websites</div>
                    <div class="divider"></div>

                    <div class="contact-list">
                        @foreach($websites as $site)
                            @php $w = $normalizeWebsite($site); @endphp
                            @if($w['url'] !== '')
                                <div class="contact-item">
                                    <span>
                                        <svg class="svg" viewBox="0 0 24 24" fill="none" style="color: {{$dynamicTextColor ?? 'rgba(255,255,255,0.95)'}};">
                                            <path d="M10 14a5 5 0 0 0 7.07 0l2.83-2.83a5 5 0 0 0-7.07-7.07L11.5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M14 10a5 5 0 0 0-7.07 0L4.1 12.83a5 5 0 0 0 7.07 7.07L12.5 19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <div class="meta">
                                        <div style="font-weight:700; color: {{$dynamicTextColor ?? 'rgba(255,255,255,0.95)'}}; margin-bottom: 2px;">{{ $w['label'] }}</div>
                                        <a href="{{ $w['url'] }}">{{ $w['url'] }}</a>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

        </aside>

        {{-- ===================== MAIN ===================== --}}
        <main class="main">

            {{-- EXPERIENCE --}}
            @if(!empty($workItems))
                <section class="section">
                    <div class="section-header">
                        <div class="icon">
                            <svg class="svg" viewBox="0 0 24 24" fill="none" style="color: var(--primary);">
                                <path d="M9 6V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M4 8h16v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8Z" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M4 12h16" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </div>
                        <h2>Experience</h2>
                    </div>

                    <div class="timeline">
                        @foreach($workItems as $i => $work)
                            @if(is_array($work))
                                @php
                                    $position = $safeText(data_get($work, 'position'));
                                    $company  = $safeText(data_get($work, 'name'));

                                    $wcity   = $safeText(data_get($work, 'location.city'));
                                    $wregion = $safeText(data_get($work, 'location.region'));
                                    $wloc    = $compactLocation($wcity, $wregion);
                                    if ($wloc === '') {
                                        $wloc = $safeText(data_get($work, 'location')) ?: $safeText(data_get($work, 'locationName'));
                                    }

                                    $range = $fmtDateRange(data_get($work, 'startDate'), data_get($work, 'endDate'));

                                    $highs = (array) data_get($work, 'highlights', []);
                                    $hasHighs = false;
                                    foreach ($highs as $h) { if (is_string($h) && trim($h) !== '') { $hasHighs = true; break; } }
                                @endphp

                                @if($position !== '' || $company !== '' || $range !== '' || $hasHighs)
                                    <div class="titem {{ $i === 0 ? 'is-primary' : '' }}">
                                        <div class="trow">
                                            <div class="tleft">
                                                <div class="t-role">{{ $position !== '' ? $position : 'Role Title' }}</div>

                                                @if($company !== '')
                                                    <div class="t-company">{{ $company }}</div>
                                                @endif

                                                @if($hasHighs)
                                                    <ul class="bullets">
                                                        @foreach($highs as $h)
                                                            @if(is_string($h) && trim($h) !== '')
                                                                <li>{{ $h }}</li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>

                                            <div class="tright">{{ $range }}</div>
                                        </div>

                                        <?php $workSummary = (string) data_get($work, 'summary', ''); ?>
                                        <?php if (trim(strip_tags($workSummary)) !== ''): ?>
                                            <div class="work-summary rich">
                                                {!! $workSummary !!}
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- EDUCATION --}}
            @if(!empty($eduItems) || !empty($certItems) || $hasCertificate)
                <section class="section">
                    <div class="section-header">
                        <div class="icon">
                            <svg class="svg" viewBox="0 0 24 24" fill="none" style="color: var(--primary);">
                                <path d="M12 3 2 8l10 5 10-5-10-5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="M6 10v6c0 1 3 3 6 3s6-2 6-3v-6" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h2>Education</h2>
                    </div>

                    <div class="edu-stack">
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
                                    <div class="card">
                                        <div class="card-row">
                                            <div>
                                                <p class="title">{{ $degree !== '' ? $degree : $institution }}</p>
                                                @if($institution !== '' && $degree !== '')
                                                    <div class="linkish">{{ $institution }}</div>
                                                @endif
                                                @if($score !== '')
                                                    <div class="meta">GPA: {{ $score }}</div>
                                                @endif
                                            </div>
                                            <div class="meta" style="font-weight:700;">{{ $range }}</div>
                                        </div>
                                    </div>
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
                                        $curl   = $safeText(data_get($cert, 'url'));
                                    @endphp

                                    @if($cname !== '' || $issuer !== '' || $date !== '')
                                        <div class="card">
                                            <div class="card-row">
                                                <div>
                                                    <p class="title">{{ $cname !== '' ? $cname : 'Certificate' }}</p>
                                                    @if($issuer !== '')
                                                        <div class="linkish">{{ $issuer }}</div>
                                                    @endif
                                                    @if($curl !== '')
                                                        <div class="meta"><a style="color: var(--muted2);" href="{{ $curl }}">{{ $curl }}</a></div>
                                                    @endif
                                                </div>
                                                <div class="meta" style="font-weight:700;">{{ $date }}</div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        @endif

                        @if(empty($certItems) && $hasCertificate)
                            <div class="card">
                                <div class="rich">{!! $certificateBody !!}</div>
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            {{-- OPTIONAL RICH SECTIONS --}}
            @if($hasProject)
                <section class="section">
                    <div class="section-header">
                        <div class="icon">
                            <svg class="svg" viewBox="0 0 24 24" fill="none" style="color: var(--primary);">
                                <path d="M4 7h16M4 12h10M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h2>Projects</h2>
                    </div>
                    <div class="card"><div class="rich">{!! $projectBody !!}</div></div>
                </section>
            @endif

            @if($hasAccomplishment)
                <section class="section">
                    <div class="section-header">
                        <div class="icon">
                            <svg class="svg" viewBox="0 0 24 24" fill="none" style="color: var(--primary);">
                                <path d="M12 3l3 6 6 .8-4.5 4.2 1.2 6L12 17l-5.7 3 1.2-6L3 9.8 9 9l3-6Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h2>Accomplishments</h2>
                    </div>
                    <div class="card"><div class="rich">{!! $accomplishmentBody !!}</div></div>
                </section>
            @endif

            @if($hasVolunteer)
                <section class="section">
                    <div class="section-header">
                        <div class="icon">
                            <svg class="svg" viewBox="0 0 24 24" fill="none" style="color: var(--primary);">
                                <path d="M12 21s-7-4.5-7-10a4 4 0 0 1 7-2 4 4 0 0 1 7 2c0 5.5-7 10-7 10Z" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </div>
                        <h2>Volunteer</h2>
                    </div>
                    <div class="card"><div class="rich">{!! $volunteerBody !!}</div></div>
                </section>
            @endif

            @if($hasAffiliation)
                <section class="section">
                    <div class="section-header">
                        <div class="icon">
                            <svg class="svg" viewBox="0 0 24 24" fill="none" style="color: var(--primary);">
                                <path d="M7 20v-2a4 4 0 0 1 4-4h2a4 4 0 0 1 4 4v2" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </div>
                        <h2>Affiliations</h2>
                    </div>
                    <div class="card"><div class="rich">{!! $affiliationBody !!}</div></div>
                </section>
            @endif

            @if($hasInterest)
                <section class="section">
                    <div class="section-header">
                        <div class="icon">
                            <svg class="svg" viewBox="0 0 24 24" fill="none" style="color: var(--primary);">
                                <path d="M4 19c5-8 11-8 16 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="M7 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M17 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </div>
                        <h2>Interests</h2>
                    </div>
                    <div class="card"><div class="rich">{!! $interestBody !!}</div></div>
                </section>
            @endif

            {{-- REFERENCES --}}
            @if(!empty($refItems))
                <section class="section" style="margin-bottom: 0;">
                    <div class="section-header">
                        <div class="icon">
                            <svg class="svg" viewBox="0 0 24 24" fill="none" style="color: var(--primary);">
                                <path d="M16 11a4 4 0 1 0-8 0" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M3 21v-1a6 6 0 0 1 6-6h6a6 6 0 0 1 6 6v1" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </div>
                        <h2>References</h2>
                    </div>

                    <div class="refs">
                        @foreach($refItems as $r)
                            @if(is_array($r))
                                @php
                                    $refName = $safeText(data_get($r, 'name'));
                                    $refBody = (string) data_get($r, 'reference', '');

                                    $refTitle   = $safeText(data_get($r, 'title')) ?: $safeText(data_get($r, 'position'));
                                    $refCompany = $safeText(data_get($r, 'company')) ?: $safeText(data_get($r, 'organization'));
                                    $refEmail   = $safeText(data_get($r, 'email'));
                                    $refPhone   = $safeText(data_get($r, 'phone'));
                                @endphp

                                @if($refName !== '' || trim(strip_tags($refBody)) !== '' || $refEmail !== '' || $refPhone !== '' || $refTitle !== '' || $refCompany !== '')
                                    <div class="card" style="background: #FFFFFF;">
                                        <p class="ref-name">{{ $refName !== '' ? $refName : 'Reference Name' }}</p>

                                        @if($refTitle !== '' || $refCompany !== '')
                                            <div class="ref-sub">
                                                {{ trim(($refTitle !== '' ? $refTitle : '') . ($refTitle !== '' && $refCompany !== '' ? ', ' : '') . ($refCompany !== '' ? $refCompany : '')) }}
                                            </div>
                                        @endif

                                        <div class="ref-lines">
                                            @if($refEmail !== '')
                                                <div class="ref-line">
                                                    <svg class="svg" viewBox="0 0 24 24" fill="none" style="color: var(--primary);">
                                                        <path d="M4 6h16v12H4V6Z" stroke="currentColor" stroke-width="1.8" />
                                                        <path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.8" />
                                                    </svg>
                                                    <div>{{ $refEmail }}</div>
                                                </div>
                                            @endif

                                            @if($refPhone !== '')
                                                <div class="ref-line">
                                                    <svg class="svg" viewBox="0 0 24 24" fill="none" style="color: var(--primary);">
                                                        <path d="M7 4h3l1 5-2 1c1 3 3 5 6 6l1-2 5 1v3c0 1-1 2-2 2-8 0-15-7-15-15 0-1 1-2 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                                    </svg>
                                                    <div>{{ $refPhone }}</div>
                                                </div>
                                            @endif

                                            @if(trim(strip_tags($refBody)) !== '')
                                                <div class="rich" style="color: var(--muted2);">{!! $refBody !!}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif

        </main>
    </div>
</div>
