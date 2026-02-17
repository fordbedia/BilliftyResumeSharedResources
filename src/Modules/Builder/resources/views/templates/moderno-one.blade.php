{{-- shared-resources/src/Modules/Builder/resources/views/templates/moderno-one.blade.php --}}
@php
    // Expecting $resume shaped like ResumeJsonResource output:
    // $resume['basics'], $resume['work'], $resume['education'], $resume['skills'], $resume['references']

    $basics = data_get($resume ?? [], 'basics', []);
    $location = data_get($basics, 'location', []);
    $profiles = data_get($basics, 'profiles', []);

    $skills = (array) data_get($resume ?? [], 'skills', []);
    $work = (array) data_get($resume ?? [], 'work', []);
    $education = (array) data_get($resume ?? [], 'education', []);
    $references = (array) data_get($resume ?? [], 'references', []);

    // Split name like the PDF: first line = first token, second line = the rest
    $fullName = trim((string) data_get($basics, 'name', ''));
    $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $nameFirst = strtoupper($nameParts[0] ?? $fullName);
    $nameRest = strtoupper(implode(' ', array_slice($nameParts, 1)));

    $previewColorScheme = $previewColorScheme ?? null;
    $colorScheme = $previewColorScheme ?? data_get($resume, 'colorScheme', '#5f8b70');

    // Contact lines (match the PDF order)
    $contactLine1 = trim(implode(' ', array_filter([
        data_get($location, 'city'),
        data_get($location, 'region'),
        data_get($location, 'postalCode'),
    ])));

    $email = (string) data_get($basics, 'email');
    $phone = (string) data_get($basics, 'phone');

    $url = (string) data_get($basics, 'url');
    $profileUrl = (string) data_get($profiles, '0.url');

    $label = (string) data_get($basics, 'label');
    $summary = (string) data_get($basics, 'summary');

    // Small helper: show date range exactly as stored
    $fmtRange = function ($start, $end) {
        $start = trim((string) $start);
        $end = trim((string) $end);
        if ($start && $end) return $start . ' - ' . $end;
        return $start ?: $end;
    };

    // ===== New props (top sections) =====
    $accomplishmentActive = (bool) data_get($resume, 'accomplishment.is_active');
    $affiliationActive    = (bool) data_get($resume, 'affiliation.is_active');
    $certificateActive    = (bool) data_get($resume, 'certificate.is_active');
    $interestActive       = (bool) data_get($resume, 'interest.is_active');
    $volunteerActive      = (bool) data_get($resume, 'volunteer.is_active');
    $projectActive        = (bool) data_get($resume, 'project.is_active');

    $accomplishmentBody = (string) data_get($resume, 'accomplishment.body', '');
    $affiliationBody    = (string) data_get($resume, 'affiliation.body', '');
    $certificate        = (string) data_get($resume, 'certificate.body', '');
    $interestBody       = (string) data_get($resume, 'interest.body', '');
    $volunteerBody      = (string) data_get($resume, 'volunteer.body', '');
    $projectBody        = (string) data_get($resume, 'project.body', '');

    // ===== Sidebar extras =====
    $languagesActive = (bool) data_get($resume, 'languages.is_active');
    $languages = (array) data_get($resume, 'languages.languages', []);
    $websitesActive = (bool) data_get($resume, 'websites.is_active');
    $websites = (array) data_get($resume, 'websites.websites', []);

    // Helper: safe trimming
    $t = function ($v) { return trim((string) $v); };

@endphp

<style>
    /* Playwright-friendly (modern CSS allowed) */
    @page { margin: 0 28px 28px 28px; }
    * { box-sizing: border-box; }
    html, body { height: 100%; }
    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        color: #2b2b2b;
        font-size: 12px;
        line-height: 1.45;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    :root {
        --accent: {{ $colorScheme ?? '#5f8b70' }};
        --ink: #2b2b2b;
        --muted: #6b6b6b;
        --soft: #f2f4f7;
        --card: #ffffff;
        --cardBorder: rgba(0,0,0,0.08);
        --shadow: 0 10px 26px rgba(0,0,0,0.08);
        --radius: 14px;
        --radiusSm: 10px;
    }

    .green { color: var(--accent); }
    .muted { color: var(--muted); }

    .name-font { font-family: DejaVu Serif, Georgia, serif; color: var(--accent); }
    .title-font { font-family: DejaVu Serif, Georgia, serif; }

    .h-name-1,
    .h-name-2 {
        font-size: 58px;
        line-height: 0.92;
        font-weight: 700;
        letter-spacing: 1px;
        margin: 0;
        padding: 0;
        color: var(--accent);
    }

    /* Right contact */
    .contact {
        font-size: 13px;
        line-height: 1.5;
        text-align: left;
        color: var(--accent);
    }
    .contact .line { margin: 0; padding: 0; }

    /* Summary band (keep original concept but polish) */
    .summary-band {
        background: linear-gradient(180deg, #f1f1f1 0%, #ededed 100%);
        padding: 14px 16px;
        margin-top: 12px;
        border-radius: var(--radius);
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 10px 22px rgba(0,0,0,0.05);
    }
    .summary-text {
        font-size: 13px;
        line-height: 1.55;
        margin: 0;
        color: #2b2b2b;
    }

    /* Layout table stays (original concept) */
    .layout {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .layout td { vertical-align: top; }

    /* Sidebar stays green (original concept) but enhanced */
    .sidebar {
        background: var(--accent);
        color: #ffffff;
        padding: 16px 14px;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        position: relative;
    }
    /* subtle decorative sheen */
    .sidebar:before {
        content: "";
        position: absolute;
        inset: -80px -120px auto auto;
        width: 220px;
        height: 220px;
        background: rgba(255,255,255,0.12);
        border-radius: 999px;
        transform: rotate(14deg);
        pointer-events: none;
    }
    .sidebar > * { position: relative; z-index: 1; }

    .section-title-sidebar {
        font-family: DejaVu Serif, Georgia, serif;
        font-size: 26px;
        font-weight: 700;
        margin: 0 0 10px 0;
        padding: 0;
        letter-spacing: 0.2px;
    }

    .section-title-main {
        font-family: DejaVu Serif, Georgia, serif;
        font-size: 26px;
        font-weight: 700;
        color: var(--accent);
        margin: 0 0 10px 0;
        padding: 0;
        letter-spacing: 0.2px;
    }

    /* Sidebar skills list (keep bullets but refine) */
    .skills-ul {
        margin: 0;
        padding: 0 0 0 16px;
    }
    .skills-ul li {
        margin: 0;
        padding: 7px 0 0 0;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    /* Dividers */
    .divider {
        border-top: 2px solid rgba(255,255,255,0.9);
        margin: 14px 0 16px 0;
        height: 0;
        line-height: 0;
        opacity: 0.95;
    }
    .divider-content {
        border-top: 2px solid var(--accent);
        margin: 14px 0 14px 0;
        height: 0;
        line-height: 0;
        opacity: 0.95;
    }

    /* Education in sidebar (keep original typography) */
    .edu-item { margin: 0 0 18px 0; }
    .edu-line1 { font-size: 16px; font-weight: 700; margin: 0 0 4px 0; }
    .edu-line2 { font-size: 15px; font-weight: 700; text-transform: uppercase; margin: 0 0 6px 0; }
    .edu-line3 { font-size: 13px; margin: 0; opacity: 0.95; }

    /* NEW: Sidebar "cards" for Websites / Languages to stand out */
    .sb-card {
        border: 1px solid rgba(255,255,255,0.28);
        background: rgba(255,255,255,0.12);
        border-radius: 12px;
        padding: 10px 10px;
        margin: 8px 0 10px 0;
        break-inside: avoid;
        page-break-inside: avoid;
    }
    .sb-card .sb-item {
        margin: 0 0 8px 0;
        padding: 0;
        font-size: 13px;
        line-height: 1.35;
        color: rgba(255,255,255,0.95);
        word-break: break-word;
    }
    .sb-card .sb-item:last-child { margin-bottom: 0; }
    .sb-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: rgba(255,255,255,0.9);
        margin-right: 8px;
        transform: translateY(-1px);
    }
    .sb-link {
        text-decoration: underline;
        color: rgba(255,255,255,0.98);
    }

    /* Main content (original concept) */
    .main {
        padding: 16px 16px 16px 18px;
    }

    .job-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-bottom: 18px;
    }
    .job-table td { vertical-align: top; }

    .job-dates {
        width: 26%;
        font-size: 13px;
        color: #6b6b6b;
        padding-right: 10px;
        font-weight: 600;
    }

    .job-title {
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 2px 0;
        color: #222;
    }
    .job-company {
        font-size: 13px;
        font-style: italic;
        color: #6b6b6b;
        margin: 0 0 10px 0;
    }

    .bullets {
        margin: 0;
        padding: 0 0 0 18px;
        color: #555555;
    }
    .bullets li {
        margin: 0 0 10px 0;
        padding: 0;
    }

    /* NEW: Make top inserted sections feel like part of the template (still in main flow) */
    .mini-section {
        margin: 12px 0 0 0;
        break-inside: avoid;
        page-break-inside: avoid;
    }
    .mini-section .mini-head {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 8px 0;
    }
    .mini-section .mini-title {
        font-family: DejaVu Serif, Georgia, serif;
        font-size: 20px;
        font-weight: 800;
        color: var(--accent);
        margin: 0;
        padding: 0;
        letter-spacing: 0.2px;
    }
    .mini-section .mini-line {
        height: 8px;
        flex: 1;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--accent), rgba(0,0,0,0.08));
        opacity: 0.9;
    }
    .mini-section .mini-body {
        border: 1px solid rgba(0,0,0,0.08);
        background: linear-gradient(180deg, #ffffff 0%, #fafafa 100%);
        border-radius: var(--radius);
        padding: 10px 12px;
        box-shadow: 0 10px 22px rgba(0,0,0,0.06);
    }
    /* normalize rich text inside body props */
    .mini-body p { margin: 0 0 10px 0; }
    .mini-body p:last-child { margin-bottom: 0; }
    .mini-body ul, .mini-body ol { margin: 0 0 10px 0; padding-left: 18px; }
    .mini-body li { margin: 0 0 8px 0; }
    .mini-body a { color: var(--accent); text-decoration: underline; }

    /* References */
    .refs-item {
        margin: 0 0 14px 0;
        padding: 0;
    }
    .ref-name {
        font-size: 14px;
        font-weight: 700;
        margin: 0 0 4px 0;
    }
    .ref-text {
        margin: 0;
        color: #555555;
        white-space: pre-line;
    }
</style>

{{-- HEADER (name left, contact right) --}}
<table class="layout">
    <tr>
        <td style="width: 70%; padding-right: 16px;">
            <div class="name-font">
                <p class="h-name-1">{{ $nameFirst }}</p>
                <p class="h-name-2">{{ $nameRest }}</p>
            </div>
        </td>
        <td style="width: 30%;">
            <div class="contact">
                @if($contactLine1)
                    <p class="line">{{ $contactLine1 }}</p>
                @endif
                @if($email)
                    <p class="line">{{ $email }}</p>
                @endif
                @if($phone)
                    <p class="line">{{ $phone }}</p>
                @endif
                @if($url)
                    <p class="line">WWW: {{ $url }}</p>
                @endif
                @if($profileUrl)
                    <p class="line">WWW: {{ $profileUrl }}</p>
                @endif
            </div>
        </td>
    </tr>
</table>

{{-- SUMMARY BAND --}}
<div class="summary-band">
    <p class="summary-text">
        @if($label)
            <span style="font-weight:700;">{{ $label }}</span>
            @if($summary) — @endif
        @endif
        {!! $summary !!}
    </p>
</div>

{{-- TOP INSERTED SECTIONS (keep original flow; just more "designed") --}}
@if ($accomplishmentActive && $t($accomplishmentBody) !== '')
    <div class="mini-section">
        <div class="mini-head">
            <p class="mini-title">Accomplishments</p>
            <span class="mini-line"></span>
        </div>
        <div class="mini-body">{!! $accomplishmentBody !!}</div>
    </div>
@endif

@if ($affiliationActive && $t($affiliationBody) !== '')
    <div class="mini-section">
        <div class="mini-head">
            <p class="mini-title">Affiliations</p>
            <span class="mini-line"></span>
        </div>
        <div class="mini-body">{!! $affiliationBody !!}</div>
    </div>
@endif

@if ($certificateActive && $t($certificate) !== '')
    <div class="mini-section">
        <div class="mini-head">
            <p class="mini-title">Certifications</p>
            <span class="mini-line"></span>
        </div>
        <div class="mini-body">{!! $certificate !!}</div>
    </div>
@endif

@if ($interestActive && $t($interestBody) !== '')
    <div class="mini-section">
        <div class="mini-head">
            <p class="mini-title">Interest</p>
            <span class="mini-line"></span>
        </div>
        <div class="mini-body">{!! $interestBody !!}</div>
    </div>
@endif

@if ($volunteerActive && $t($volunteerBody) !== '')
    <div class="mini-section">
        <div class="mini-head">
            <p class="mini-title">Volunteer</p>
            <span class="mini-line"></span>
        </div>
        <div class="mini-body">{!! $volunteerBody !!}</div>
    </div>
@endif

{{-- BODY: LEFT GREEN SIDEBAR + RIGHT MAIN (original concept) --}}
<table class="layout" style="margin-top: 14px;">
    <tr>
        {{-- SIDEBAR --}}
        <td class="sidebar" style="width: 34%;">
            <p class="section-title-sidebar">Skills</p>

            @if(!empty($skills))
                <div class="skills">
                    <ul class="skills-ul">
                        @foreach($skills as $s)
                            @php $skillName = (string) data_get($s, 'name'); @endphp
                            @if($skillName !== '')
                                <li>{{ $skillName }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="divider"></div>

            <p class="section-title-sidebar">Education</p>

            @foreach($education as $e)
                @php
                    $institution = (string) data_get($e, 'institution');
                    $area = (string) data_get($e, 'area');
                    $studyType = (string) data_get($e, 'studyType');
                    $endDate = (string) data_get($e, 'endDate');
                    $startDate = (string) data_get($e, 'startDate');
                @endphp

                <div class="edu-item">
                    @if($studyType)
                        <p class="edu-line1">{{ $studyType }}</p>
                    @endif

                    @if($area)
                        <p class="edu-line2">{{ $area }}</p>
                    @endif

                    <p class="edu-line3">
                        {{ $institution }}
                        @if($endDate || $startDate)
                            | {{ $fmtRange($startDate, $endDate) }}
                        @endif
                    </p>
                </div>
            @endforeach

            {{-- Languages (enhanced card list) --}}
            @if (!empty($languages) && $languagesActive)
                <div class="divider"></div>
                <p class="section-title-sidebar">Languages</p>

                <div class="sb-card">
                    @foreach ($languages as $language)
                        @php $lang = $t(data_get($language, 'language')); @endphp
                        @if ($lang !== '')
                            <p class="sb-item"><span class="sb-dot"></span>{{ $lang }}</p>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- Websites/Portfolio (enhanced card list) --}}
            @if (!empty($websites) && $websitesActive)
                <div class="divider"></div>
                <p class="section-title-sidebar">Websites/Portfolio</p>

                <div class="sb-card">
                    @foreach ($websites as $website)
                        @php $w = $t(data_get($website, 'url')); @endphp
                        @if ($w !== '')
                            <p class="sb-item">
                                <span class="sb-dot"></span>
                                <span class="sb-link">{{ $w }}</span>
                            </p>
                        @endif
                    @endforeach
                </div>
            @endif

        </td>

        {{-- MAIN CONTENT --}}
        <td class="main" style="width: 66%;">
            <p class="section-title-main">Work history</p>

            @foreach($work as $w)
                @php
                    $company = (string) data_get($w, 'name');
                    $workSummary = (string) data_get($w, 'summary');
                    $position = (string) data_get($w, 'position');
                    $startDate = (string) data_get($w, 'startDate');
                    $endDate = (string) data_get($w, 'endDate');
                    $highlights = (array) (data_get($w, 'highlights') ?? []);
                @endphp

                <table class="job-table">
                    <tr>
                        <td class="job-dates">
                            {{ $fmtRange($startDate, $endDate) }}
                        </td>
                        <td>
                            <p class="job-title">{{ $position }}</p>
                            <p class="job-company">{{ $company }}</p>

                            @if($t(strip_tags($workSummary)) !== '')
                                <p>{!! $workSummary !!}</p>
                            @endif

                            @if(!empty($highlights))
                                <ul class="bullets">
                                    @foreach($highlights as $h)
                                        @php $h = trim((string) $h); @endphp
                                        @if($h !== '')
                                            <li>{{ $h }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                    </tr>
                </table>
            @endforeach

            {{-- Projects (keep original placement, but make it stand out) --}}
            @if ($projectActive && $t($projectBody) !== '')
                <div class="mini-section" style="margin-top: 6px;">
                    <div class="mini-head">
                        <p class="mini-title">Projects</p>
                        <span class="mini-line"></span>
                    </div>
                    <div class="mini-body">{!! $projectBody !!}</div>
                </div>
            @endif

            <p class="section-title-main" style="margin-top: 14px;">References</p>

            @foreach($references as $r)
                @php
                    $rName = (string) data_get($r, 'name');
                    $rText = (string) data_get($r, 'reference');
                @endphp

                <div class="refs-item">
                    <p class="ref-name">{{ $rName }}</p>
                    <p class="ref-text">{!! $rText !!}</p>
                </div>
            @endforeach
        </td>
    </tr>
</table>
