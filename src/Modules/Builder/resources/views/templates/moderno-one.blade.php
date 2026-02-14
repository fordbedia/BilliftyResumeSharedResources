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

    $fullName = trim((string) data_get($basics, 'name', ''));
    $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $nameFirst = $nameParts[0] ?? $fullName;
    $nameRest = implode(' ', array_slice($nameParts, 1));

    $previewColorScheme = $previewColorScheme ?? null;
    $colorScheme = $previewColorScheme ?? data_get($resume, 'colorScheme', '#5f8b70');

    $contactLine1 = trim(implode(', ', array_filter([
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

    $fmtRange = function ($start, $end) {
        $start = trim((string) $start);
        $end = trim((string) $end);
        if ($start && $end) return $start . ' — ' . $end;
        return $start ?: $end;
    };

    // New sections (rich text bodies)
    $accomplishmentActive = (bool) data_get($resume, 'accomplishment.is_active');
    $affiliationActive    = (bool) data_get($resume, 'affiliation.is_active');
    $interestActive       = (bool) data_get($resume, 'interest.is_active');
    $volunteerActive      = (bool) data_get($resume, 'volunteer.is_active');
    $projectActive        = (bool) data_get($resume, 'project.is_active');

    $accomplishmentBody = (string) data_get($resume, 'accomplishment.body', '');
    $affiliationBody    = (string) data_get($resume, 'affiliation.body', '');
    $interestBody       = (string) data_get($resume, 'interest.body', '');
    $volunteerBody      = (string) data_get($resume, 'volunteer.body', '');
    $projectBody        = (string) data_get($resume, 'project.body', '');

    // Sidebar extras
    $languagesActive = (bool) data_get($resume, 'languages.is_active');
    $languages = (array) data_get($resume, 'languages.languages', []);

    $websitesActive = (bool) data_get($resume, 'websites.is_active');
    $websites = (array) data_get($resume, 'websites.websites', []);

    // Helpers
    $hasAnyTopSections =
        ($accomplishmentActive && trim($accomplishmentBody) !== '') ||
        ($affiliationActive && trim($affiliationBody) !== '') ||
        ($interestActive && trim($interestBody) !== '') ||
        ($volunteerActive && trim($volunteerBody) !== '');

    $safeText = function ($value) {
        return trim((string) $value);
    };
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <style>
        /* ====== Print / PDF ====== */
        @page { margin: 26px; }
        html, body { height: 100%; }
        * { -webkit-print-color-adjust: exact; print-color-adjust: exact; box-sizing: border-box; }

        :root{
            --accent: {{ $colorScheme ?? '#5f8b70' }};
            --ink: #1f2937;
            --muted: #6b7280;
            --soft: #eef2f7;
            --paper: #ffffff;
            --sidebar-ink: rgba(255,255,255,0.92);
            --sidebar-ink-2: rgba(255,255,255,0.78);
            --shadow: 0 10px 26px rgba(17,24,39,0.10);
            --radius: 18px;
            --radius-sm: 12px;
        }

        body {
            margin: 0;
            font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif;
            color: var(--ink);
            font-size: 12.5px;
            line-height: 1.55;
            background: var(--paper);
        }

        /* ====== Page wrapper ====== */
        .page {
            width: 100%;
        }

        /* ====== Header ====== */
        .header {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 18px;
            align-items: start;
        }

        .name {
            margin: 0;
            padding: 0;
        }
        .name .first {
            font-family: ui-serif, Georgia, "Times New Roman", Times, serif;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: var(--accent);
            font-size: 52px;
            line-height: 0.95;
            margin: 0;
        }
        .name .rest {
            font-family: ui-serif, Georgia, "Times New Roman", Times, serif;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: var(--accent);
            font-size: 52px;
            line-height: 0.95;
            margin: 0;
        }

        .contactCard {
            border: 1px solid rgba(31,41,55,0.10);
            background: linear-gradient(180deg, rgba(255,255,255,1) 0%, rgba(248,250,252,1) 100%);
            border-radius: var(--radius);
            padding: 14px 14px 12px 14px;
            box-shadow: 0 6px 18px rgba(17,24,39,0.06);
        }

        .contactLine {
            margin: 0;
            padding: 0;
            color: var(--muted);
            font-size: 12.5px;
            line-height: 1.45;
        }
        .contactLine strong {
            color: var(--ink);
            font-weight: 700;
        }

        /* ====== Summary ====== */
        .summaryCard {
            margin-top: 14px;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid rgba(31,41,55,0.10);
        }
        .summaryTop {
            background: linear-gradient(90deg, color-mix(in srgb, var(--accent) 88%, #000 12%) 0%, var(--accent) 48%, color-mix(in srgb, var(--accent) 78%, #fff 22%) 100%);
            padding: 10px 16px;
            color: white;
        }
        .summaryTop .label {
            font-weight: 800;
            letter-spacing: 0.4px;
            font-size: 13px;
            margin: 0;
        }
        .summaryBody {
            background: #ffffff;
            padding: 12px 16px 14px 16px;
        }
        .summaryBody p { margin: 0; }
        .summaryBody * { max-width: 100%; }

        /* ====== Highlight sections (Accomplishments/Affiliations/Interest/Volunteer/Projects) ====== */
        .featureGrid {
            margin-top: 14px;
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 12px;
        }

        .feature {
            grid-column: span 6;
            border-radius: var(--radius);
            border: 1px solid rgba(31,41,55,0.10);
            background: linear-gradient(180deg, #ffffff 0%, rgba(248,250,252,1) 100%);
            box-shadow: 0 8px 24px rgba(17,24,39,0.07);
            overflow: hidden;
        }

        .featureHeader {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            background: linear-gradient(90deg,
                color-mix(in srgb, var(--accent) 14%, #fff 86%) 0%,
                color-mix(in srgb, var(--accent) 8%, #fff 92%) 100%);
            border-bottom: 1px solid rgba(31,41,55,0.08);
        }

        .featureTitle {
            margin: 0;
            font-family: ui-serif, Georgia, "Times New Roman", Times, serif;
            font-weight: 900;
            font-size: 16px;
            letter-spacing: 0.3px;
            color: var(--ink);
        }

        .featurePill {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            color: white;
            background: var(--accent);
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .featureBody {
            padding: 12px 14px 14px 14px;
            color: #374151;
        }
        .featureBody p { margin: 0 0 10px 0; }
        .featureBody ul, .featureBody ol { margin: 0 0 10px 0; padding-left: 18px; }
        .featureBody li { margin: 0 0 8px 0; }
        .featureBody a { color: var(--accent); text-decoration: underline; }

        /* Make these sections never split awkwardly */
        .feature { break-inside: avoid; page-break-inside: avoid; }

        /* ====== Main two-column layout ====== */
        .content {
            margin-top: 14px;
            display: grid;
            grid-template-columns: 310px 1fr;
            gap: 14px;
            align-items: stretch;
        }

        /* ====== Sidebar ====== */
        .sidebar {
            border-radius: var(--radius);
            padding: 14px;
            background: radial-gradient(1200px 500px at -10% -20%,
                color-mix(in srgb, var(--accent) 80%, #fff 20%) 0%,
                var(--accent) 40%,
                color-mix(in srgb, var(--accent) 65%, #000 35%) 100%);
            color: var(--sidebar-ink);
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .sidebar:before {
            content: "";
            position: absolute;
            inset: -80px -120px auto auto;
            width: 220px;
            height: 220px;
            background: rgba(255,255,255,0.10);
            border-radius: 999px;
            transform: rotate(12deg);
        }

        .sbSection {
            position: relative;
            z-index: 1;
            margin-bottom: 14px;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .sbTitle {
            margin: 0 0 10px 0;
            font-family: ui-serif, Georgia, "Times New Roman", Times, serif;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 0.2px;
            color: #ffffff;
        }

        .sbDivider {
            height: 1px;
            background: rgba(255,255,255,0.28);
            margin: 12px 0;
        }

        /* Skills as "tags" */
        .tagWrap {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .tag {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.30);
            background: rgba(255,255,255,0.10);
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.2px;
            white-space: nowrap;
        }

        /* Education cards */
        .eduCard {
            border: 1px solid rgba(255,255,255,0.22);
            background: rgba(255,255,255,0.10);
            border-radius: var(--radius-sm);
            padding: 10px 10px;
            margin-bottom: 10px;
        }
        .eduLine1 { margin: 0; font-size: 13px; font-weight: 900; color: #fff; }
        .eduLine2 { margin: 2px 0 0 0; font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--sidebar-ink); }
        .eduLine3 { margin: 4px 0 0 0; font-size: 12px; color: var(--sidebar-ink-2); }

        /* Sidebar lists (Languages/Websites) */
        .sbList {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 8px;
        }
        .sbList li {
            border: 1px solid rgba(255,255,255,0.22);
            background: rgba(255,255,255,0.10);
            border-radius: var(--radius-sm);
            padding: 8px 10px;
            font-size: 12px;
            color: #fff;
            word-break: break-word;
        }
        .sbList a { color: #fff; text-decoration: underline; }

        /* ====== Main column ====== */
        .main {
            border-radius: var(--radius);
            border: 1px solid rgba(31,41,55,0.10);
            background: linear-gradient(180deg, #ffffff 0%, rgba(248,250,252,1) 100%);
            box-shadow: var(--shadow);
            padding: 14px 16px;
            min-height: 100%;
        }

        .mainTitleRow {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .mainTitle {
            margin: 0;
            font-family: ui-serif, Georgia, "Times New Roman", Times, serif;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 0.2px;
            color: var(--ink);
        }

        .accentLine {
            height: 10px;
            flex: 1;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--accent), rgba(31,41,55,0.08));
            opacity: 0.9;
        }

        /* Work items */
        .job {
            border: 1px solid rgba(31,41,55,0.08);
            background: #fff;
            border-radius: var(--radius);
            padding: 12px 12px 10px 12px;
            margin-bottom: 12px;
            box-shadow: 0 8px 20px rgba(17,24,39,0.05);
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .jobTop {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 12px;
            align-items: start;
            margin-bottom: 8px;
        }

        .jobDates {
            color: var(--muted);
            font-weight: 800;
            font-size: 12px;
            letter-spacing: 0.2px;
            padding-top: 2px;
        }

        .jobRole {
            margin: 0;
            font-size: 14px;
            font-weight: 900;
            color: var(--ink);
        }

        .jobCompany {
            margin: 2px 0 0 0;
            color: var(--muted);
            font-style: italic;
            font-size: 12px;
        }

        .jobSummary {
            margin: 8px 0 0 0;
            color: #374151;
        }

        .jobSummary p { margin: 0 0 10px 0; }
        .jobSummary a { color: var(--accent); text-decoration: underline; }

        .bullets {
            margin: 10px 0 0 0;
            padding-left: 18px;
            color: #374151;
        }
        .bullets li { margin: 0 0 8px 0; }

        /* References */
        .refsGrid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .refCard {
            border: 1px solid rgba(31,41,55,0.08);
            background: #ffffff;
            border-radius: var(--radius);
            padding: 12px 12px 10px 12px;
            box-shadow: 0 8px 20px rgba(17,24,39,0.05);
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .refName {
            margin: 0 0 6px 0;
            font-size: 13px;
            font-weight: 900;
            color: var(--ink);
        }
        .refText {
            margin: 0;
            color: #374151;
            white-space: pre-line;
        }

        /* Responsive-ish (helps if you test on different viewport widths before printing) */
        @media print {
            .feature { box-shadow: none; }
            .summaryCard, .sidebar, .main, .job, .refCard { box-shadow: none; }
        }
    </style>
</head>

<body>
<div class="page">

    {{-- HEADER --}}
    <div class="header">
        <div class="name">
            <p class="first">{{ $nameFirst }}</p>
            @if(trim($nameRest) !== '')
                <p class="rest">{{ $nameRest }}</p>
            @endif
        </div>

        <div class="contactCard">
            @if($contactLine1)
                <p class="contactLine"><strong>Location:</strong> {{ $contactLine1 }}</p>
            @endif
            @if($email)
                <p class="contactLine"><strong>Email:</strong> {{ $email }}</p>
            @endif
            @if($phone)
                <p class="contactLine"><strong>Phone:</strong> {{ $phone }}</p>
            @endif
            @if($url)
                <p class="contactLine"><strong>Web:</strong> {{ $url }}</p>
            @endif
            @if($profileUrl)
                <p class="contactLine"><strong>Profile:</strong> {{ $profileUrl }}</p>
            @endif
        </div>
    </div>

    {{-- SUMMARY --}}
    <div class="summaryCard">
        <div class="summaryTop">
            <p class="label">
                @if($label) {{ $label }} @else Professional Summary @endif
            </p>
        </div>
        <div class="summaryBody">
            {!! $summary !!}
        </div>
    </div>

    {{-- TOP FEATURE SECTIONS (Accomplishments / Affiliations / Interest / Volunteer) --}}
    @if ($hasAnyTopSections)
        <div class="featureGrid">
            @if ($accomplishmentActive && trim($accomplishmentBody) !== '')
                <section class="feature">
                    <div class="featureHeader">
                        <p class="featureTitle">Accomplishments</p>
                        <span class="featurePill">Highlights</span>
                    </div>
                    <div class="featureBody">{!! $accomplishmentBody !!}</div>
                </section>
            @endif

            @if ($affiliationActive && trim($affiliationBody) !== '')
                <section class="feature">
                    <div class="featureHeader">
                        <p class="featureTitle">Affiliations</p>
                        <span class="featurePill">Network</span>
                    </div>
                    <div class="featureBody">{!! $affiliationBody !!}</div>
                </section>
            @endif

            @if ($interestActive && trim($interestBody) !== '')
                <section class="feature">
                    <div class="featureHeader">
                        <p class="featureTitle">Interests</p>
                        <span class="featurePill">Personal</span>
                    </div>
                    <div class="featureBody">{!! $interestBody !!}</div>
                </section>
            @endif

            @if ($volunteerActive && trim($volunteerBody) !== '')
                <section class="feature">
                    <div class="featureHeader">
                        <p class="featureTitle">Volunteer</p>
                        <span class="featurePill">Service</span>
                    </div>
                    <div class="featureBody">{!! $volunteerBody !!}</div>
                </section>
            @endif
        </div>
    @endif

    {{-- MAIN 2-COLUMN CONTENT --}}
    <div class="content">

        {{-- SIDEBAR --}}
        <aside class="sidebar">

            <div class="sbSection">
                <p class="sbTitle">Skills</p>
                @if(!empty($skills))
                    <div class="tagWrap">
                        @foreach($skills as $s)
                            @php $skillName = $safeText(data_get($s, 'name')); @endphp
                            @if($skillName !== '')
                                <span class="tag">{{ $skillName }}</span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="sbDivider"></div>

            <div class="sbSection">
                <p class="sbTitle">Education</p>
                @foreach($education as $e)
                    @php
                        $institution = $safeText(data_get($e, 'institution'));
                        $area = $safeText(data_get($e, 'area'));
                        $studyType = $safeText(data_get($e, 'studyType'));
                        $endDate = $safeText(data_get($e, 'endDate'));
                        $startDate = $safeText(data_get($e, 'startDate'));
                    @endphp

                    <div class="eduCard">
                        @if($studyType)
                            <p class="eduLine1">{{ $studyType }}</p>
                        @endif

                        @if($area)
                            <p class="eduLine2">{{ $area }}</p>
                        @endif

                        <p class="eduLine3">
                            {{ $institution }}
                            @if($endDate || $startDate)
                                <br/>{{ $fmtRange($startDate, $endDate) }}
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>

            @if (!empty($languages) && $languagesActive)
                <div class="sbDivider"></div>
                <div class="sbSection">
                    <p class="sbTitle">Languages</p>
                    <ul class="sbList">
                        @foreach ($languages as $language)
                            @php $lang = $safeText(data_get($language, 'language')); @endphp
                            @if($lang !== '')
                                <li>{{ $lang }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (!empty($websites) && $websitesActive)
                <div class="sbDivider"></div>
                <div class="sbSection">
                    <p class="sbTitle">Websites</p>
                    <ul class="sbList">
                        @foreach ($websites as $website)
                            @php $w = $safeText(data_get($website, 'url')); @endphp
                            @if($w !== '')
                                <li>{{ $w }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif

        </aside>

        {{-- MAIN --}}
        <main class="main">

            <div class="mainTitleRow">
                <p class="mainTitle">Work History</p>
                <div class="accentLine"></div>
            </div>

            @foreach($work as $w)
                @php
                    $company = $safeText(data_get($w, 'name'));
                    $workSummary = (string) data_get($w, 'summary');
                    $position = $safeText(data_get($w, 'position'));
                    $startDate = $safeText(data_get($w, 'startDate'));
                    $endDate = $safeText(data_get($w, 'endDate'));
                    $highlights = (array) (data_get($w, 'highlights') ?? []);
                @endphp

                <section class="job">
                    <div class="jobTop">
                        <div class="jobDates">{{ $fmtRange($startDate, $endDate) }}</div>
                        <div>
                            <p class="jobRole">{{ $position }}</p>
                            <p class="jobCompany">{{ $company }}</p>
                        </div>
                    </div>

                    @if(trim(strip_tags($workSummary)) !== '')
                        <div class="jobSummary">{!! $workSummary !!}</div>
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
                </section>
            @endforeach

            {{-- PROJECTS (Styled to stand out) --}}
            @if ($projectActive && trim($projectBody) !== '')
                <div style="margin-top: 14px;">
                    <div class="mainTitleRow">
                        <p class="mainTitle">Projects</p>
                        <div class="accentLine"></div>
                    </div>

                    <section class="job" style="border-left: 6px solid var(--accent);">
                        <div class="jobSummary">{!! $projectBody !!}</div>
                    </section>
                </div>
            @endif

            {{-- REFERENCES --}}
            <div style="margin-top: 14px;">
                <div class="mainTitleRow">
                    <p class="mainTitle">References</p>
                    <div class="accentLine"></div>
                </div>

                <div class="refsGrid">
                    @foreach($references as $r)
                        @php
                            $rName = $safeText(data_get($r, 'name'));
                            $rText = (string) data_get($r, 'reference');
                        @endphp

                        <div class="refCard">
                            <p class="refName">{{ $rName }}</p>
                            <p class="refText">{!! $rText !!}</p>
                        </div>
                    @endforeach
                </div>
            </div>

        </main>
    </div>
</div>
</body>
</html>
