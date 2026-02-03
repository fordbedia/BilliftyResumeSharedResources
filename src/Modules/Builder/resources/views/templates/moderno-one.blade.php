{{-- shared-resources/src/Modules/Builder/resources/views/templates/edbedia-green.blade.php --}}
@php
    // Expecting $resume shaped like ResumeJsonResource output:
    // $resume['basics'], $resume['work'], $resume['education'], $resume['skills'], $resume['references']

    $basics = data_get($resume ?? [], 'basics', []);
    $location = data_get($basics, 'location', []);
    $profiles = data_get($basics, 'profiles', []);

    $skills = data_get($resume ?? [], 'skills', []);
    $work = data_get($resume ?? [], 'work', []);
    $education = data_get($resume ?? [], 'education', []);
    $references = data_get($resume ?? [], 'references', []);

    // Split name like the PDF: first line = first token, second line = the rest
    $fullName = trim((string) data_get($basics, 'name', ''));
    $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $nameFirst = strtoupper($nameParts[0] ?? $fullName);
    $nameRest = strtoupper(implode(' ', array_slice($nameParts, 1)));
	$previewColorScheme = $previewColorScheme ?? null;
	$colorScheme = $previewColorScheme ?? data_get($resume, 'colorScheme');

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
@endphp
    <style>
        /* Dompdf-safe: no flex/grid, keep it table-based */
        @page { margin: 0 28px 28px 28px; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #2b2b2b;
            font-size: 12px;
            line-height: 1.45;
        }

        .green { color: #5f8b70; }
        .muted { color: #6b6b6b; }

        .name-font { font-family: DejaVu Serif, Georgia, serif; color: {{$colorScheme ?? '#5f8b70'}}; }
        .title-font { font-family: DejaVu Serif, Georgia, serif; }

        .h-name-1 {
            font-size: 58px;
            line-height: 0.92;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 0;
            padding: 0;
			color: {{$colorScheme}};
        }
        .h-name-2 {
            font-size: 58px;
            line-height: 0.92;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 0;
            padding: 0;
			color: {{$colorScheme}};
        }

        .contact {
            font-size: 13px;
            line-height: 1.5;
            text-align: left;
			color: {{$colorScheme ?? '#5f8b70'}};
        }
        .contact .line { margin: 0; padding: 0; }

        .summary-band {
            background: #eeeeee;
            padding: 14px 16px;
            margin-top: 12px;
        }
        .summary-text {
            font-size: 13px;
            line-height: 1.55;
            margin: 0;
        }

        .layout {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .layout td { vertical-align: top; }

        .sidebar {
            background: {{$colorScheme ?? '#5f8b70'}};
            color: #ffffff;
            padding: 16px 14px;
        }

        .section-title-sidebar {
            font-family: DejaVu Serif, Georgia, serif;
            font-size: 26px;
            font-weight: 700;
            margin: 0 0 10px 0;
            padding: 0;
        }

        .section-title-main {
            font-family: DejaVu Serif, Georgia, serif;
            font-size: 26px;
            font-weight: 700;
            color: {{$colorScheme ?? '#5f8b70'}};
            margin: 0 0 10px 0;
            padding: 0;
        }
		.skills {

		}
        .skills-ul {
            margin: 0;
            padding: 0 0 0 16px; /* bullet indent */
        }
        .skills-ul li {
            margin: 0 0 0 0;
            padding: 8px 0 0 0;
			break-inside: avoid;
        }

        .divider {
            border-top: 2px solid #ffffff;
            margin: 14px 0 16px 0;
            height: 0;
            line-height: 0;
        }

        .edu-item { margin: 0 0 18px 0; }
        .edu-line1 { font-size: 16px; font-weight: 700; margin: 0 0 4px 0; }
        .edu-line2 { font-size: 15px; font-weight: 700; text-transform: uppercase; margin: 0 0 6px 0; }
        .edu-line3 { font-size: 13px; margin: 0; }

        .main {
            padding: 16px 16px 16px 18px;
        }

        .job-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 26px;
        }
        .job-table td { vertical-align: top; }

        .job-dates {
            width: 26%;
            font-size: 13px;
            color: #6b6b6b;
            padding-right: 10px;
        }

        .job-title {
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 2px 0;
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
            white-space: pre-line; /* dompdf supports this */
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

    {{-- BODY: LEFT GREEN SIDEBAR + RIGHT MAIN --}}
    <table class="layout" style="margin-top: 0;">
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
								<p>{!! $workSummary !!}</p>

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

                <p class="section-title-main" style="margin-top: 6px;">References</p>

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