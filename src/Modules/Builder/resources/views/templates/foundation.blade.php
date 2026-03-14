{{-- shared-resources/src/Modules/Builder/resources/views/templates/foundation.blade.php --}}

<?php
    $resume = $resume ?? [];

    $basics = (array) ($resume['basics'] ?? []);
    $previewColorScheme = $previewColorScheme ?? null;
    $colorScheme = $previewColorScheme ?? data_get($resume, 'colorScheme', '#4B5563');

    $primaryColor = '#4B5563';
    if (is_string($colorScheme)) {
        $candidate = trim($colorScheme);
        if (preg_match('/^(#[0-9a-fA-F]{3,8}|rgba?\([^)]+\)|hsla?\([^)]+\))$/', $candidate)) {
            $primaryColor = $candidate;
        }
    }

    $colorScheme = $primaryColor;
    $dynamicTextColor = contrastTextFromHsl($primaryColor);

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

    $compactLocation = function ($city, $region) use ($safeText) {
        $city   = $safeText($city);
        $region = $safeText($region);
        if ($city === '' && $region === '') return '';
        if ($city !== '' && $region !== '') return $city . ', ' . $region;
        return $city ?: $region;
    };

    $name  = $safeText(data_get($basics, 'name')) ?: 'John Doe';
    $label = $safeText(data_get($basics, 'label'));
    $email = $safeText(data_get($basics, 'email'));
    $phone = $safeText(data_get($basics, 'phone'));
    $url   = $safeText(data_get($basics, 'url'));

    $city            = $safeText(data_get($basics, 'location.city'));
    $region          = $safeText(data_get($basics, 'location.region'));
    $displayLocation = $compactLocation($city, $region);

    $profiles = (array) data_get($basics, 'profiles', []);
    $profileUrl = '';
    foreach ($profiles as $profile) {
        $candidate = $safeText(data_get($profile, 'url')) ?: $safeText(data_get($profile, 'username'));
        if ($candidate !== '') {
            $profileUrl = $candidate;
            break;
        }
    }

    $summaryHtml = (string) data_get($basics, 'summary', '');
    $skillsHtml  = (string) data_get($resume, 'skills.body', '');

    $workItems = (array) data_get($resume, 'work', []);
    $eduItems  = (array) data_get($resume, 'education', []);
    $jsonProjects = (array) data_get($resume, 'projects', []);

    $projectActive = (bool) data_get($resume, 'project.is_active');
    $projectBody   = (string) data_get($resume, 'project.body', '');
    $hasProjectBody = $projectActive && trim(strip_tags($projectBody)) !== '';

    $iconColor = $colorScheme;
?>

<style>
    @page { margin: 0; }

    html, body{
        margin: 0;
        padding: 0;
        width: 100%;
    }

    * { box-sizing: border-box; }

    body{
        font-family: Arial, "Helvetica Neue", Helvetica, "DejaVu Sans", sans-serif;
        font-size: 13px;
        line-height: 1.42;
        color: #374151;
        background: #E5E7EB;
    }

    .sheet{
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        background: #FFFFFF;
        padding: 20mm 12mm 16mm;
        display: flex;
        flex-direction: column;
    }

    .name{
        margin: 0;
        font-size: 46px;
        line-height: 1.05;
        color: #111827;
        font-weight: 700;
        letter-spacing: 0.1px;
    }

    .title{
        margin-top: 6px;
        font-size: 28px;
        line-height: 1.1;
        font-weight: 700;
        color: {{ $colorScheme }};
    }

    .contact-row{
        margin-top: 12px;
        display: flex;
        flex-wrap: wrap;
        gap: 14px 18px;
        align-items: center;
        color: #6B7280;
        font-size: 13px;
    }

    .contact-item{
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }

    .contact-item a{
        color: #6B7280;
        text-decoration: none;
        word-break: break-word;
    }

    .icon{
        width: 12px;
        height: 12px;
        color: {{ $iconColor }};
        flex: 0 0 auto;
    }

    .rule{
        margin-top: 13px;
        border-top: 1px solid {{ $colorScheme }};
    }

    .section{
        margin-top: 18px;
    }

    .section-title{
        margin: 0;
        font-size: 32px;
        line-height: 1.08;
        font-weight: 700;
        color: #111827;
    }

    .section-rule{
        margin-top: 7px;
        border-top: 1px solid {{ $colorScheme }};
    }

    .section-body{
        margin-top: 8px;
        color: #374151;
        font-size: 13px;
    }

    .section-body p{
        margin: 0 0 7px 0;
    }

    .job{
        margin-top: 14px;
    }

    .job:first-child{
        margin-top: 2px;
    }

    .job-head{
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: flex-start;
    }

    .job-role{
        font-size: 26px;
        line-height: 1.1;
        color: #111827;
        font-weight: 700;
    }

    .job-meta{
        white-space: nowrap;
        color: {{ $colorScheme }};
        font-weight: 700;
        font-size: 14px;
        margin-top: 2px;
    }

    .job-company{
        margin-top: 2px;
        font-size: 15px;
        line-height: 1.2;
        color: {{ $colorScheme }};
        font-weight: 700;
    }

    .job-highlights{
        margin: 8px 0 0 0;
        padding-left: 18px;
        color: #374151;
    }

    .job-highlights li{
        margin: 0 0 5px 0;
    }

    .edu-item{
        margin-top: 10px;
    }

    .edu-head{
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: flex-start;
    }

    .edu-degree{
        font-size: 28px;
        line-height: 1.1;
        color: #111827;
        font-weight: 700;
    }

    .edu-school{
        margin-top: 3px;
        color: #374151;
        font-size: 14px;
        font-weight: 700;
    }

    .edu-score{
        margin-top: 2px;
        color: #6B7280;
        font-size: 13px;
    }

    .project-item{
        margin-top: 10px;
    }

    .project-head{
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: baseline;
    }

    .project-title{
        font-size: 27px;
        line-height: 1.1;
        color: #111827;
        font-weight: 700;
    }

    .project-link{
        color: {{ $colorScheme }};
        font-weight: 700;
        font-size: 14px;
    }

    .rich ul { margin: 7px 0 0 18px; padding: 0; }
    .rich li { margin: 0 0 5px 0; }
</style>

<div class="sheet">
    <h1 class="name" style="{{ $sectionOrderStyle('basics') }}">{{ $name }}</h1>

    @if($label !== '')
        <div class="title" style="{{ $sectionOrderStyle('basics') }}">{{ $label }}</div>
    @endif

    <div class="contact-row" style="{{ $sectionOrderStyle('basics') }}">
        @if($email !== '')
            <div class="contact-item">
                <svg class="icon" viewBox="0 0 24 24" fill="none">
                    <path d="M4 6h16v12H4V6Z" stroke="currentColor" stroke-width="1.8"/>
                    <path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.8"/>
                </svg>
                <a href="mailto:{{ $email }}">{{ $email }}</a>
            </div>
        @endif

        @if($phone !== '')
            <div class="contact-item">
                <svg class="icon" viewBox="0 0 24 24" fill="none">
                    <path d="M7 4h3l1 5-2 1c1 3 3 5 6 6l1-2 5 1v3c0 1-1 2-2 2-8 0-15-7-15-15 0-1 1-2 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                </svg>
                <span>{{ $phone }}</span>
            </div>
        @endif

        @if($displayLocation !== '')
            <div class="contact-item">
                <svg class="icon" viewBox="0 0 24 24" fill="none">
                    <path d="M12 21s7-6 7-12a7 7 0 1 0-14 0c0 6 7 12 7 12Z" stroke="currentColor" stroke-width="1.8"/>
                    <path d="M12 11.5A2.5 2.5 0 1 0 12 6.5a2.5 2.5 0 0 0 0 5Z" stroke="currentColor" stroke-width="1.8"/>
                </svg>
                <span>{{ $displayLocation }}</span>
            </div>
        @endif

        @if($profileUrl !== '')
            <div class="contact-item">
                <svg class="icon" viewBox="0 0 24 24" fill="none">
                    <path d="M4 4h16v16H4V4Z" stroke="currentColor" stroke-width="1.8"/>
                    <path d="M8 11v7" stroke="currentColor" stroke-width="1.8"/>
                    <path d="M8 8.5v.5" stroke="currentColor" stroke-width="1.8"/>
                    <path d="M12 18v-4c0-2 3-2 3 0v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <a href="{{ $profileUrl }}">{{ $profileUrl }}</a>
            </div>
        @endif

        @if($profileUrl === '' && $url !== '')
            <div class="contact-item">
                <svg class="icon" viewBox="0 0 24 24" fill="none">
                    <path d="M2 12h20" stroke="currentColor" stroke-width="1.8"/>
                    <path d="M12 2c3.5 3 3.5 17 0 20" stroke="currentColor" stroke-width="1.8"/>
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                </svg>
                <a href="{{ $url }}">{{ $url }}</a>
            </div>
        @endif
    </div>

    <div class="rule" style="{{ $sectionOrderStyle('basics') }}"></div>

    @if(trim(strip_tags($summaryHtml)) !== '')
        <section class="section" style="{{ $sectionOrderStyle('basics') }}">
            <h2 class="section-title">Professional Summary</h2>
            <div class="section-rule"></div>
            <div class="section-body rich">{!! $summaryHtml !!}</div>
        </section>
    @endif

    @if(!empty($workItems))
        <section class="section" style="{{ $sectionOrderStyle('work') }}">
            <h2 class="section-title">Work Experience</h2>
            <div class="section-rule"></div>

            @foreach($workItems as $work)
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
                        $metaParts = array_values(array_filter([$wloc, $range], fn($v) => $v !== ''));
                        $metaText = implode(' | ', $metaParts);
                        $highs = (array) data_get($work, 'highlights', []);
                    @endphp

                    @if($position !== '' || $company !== '' || $metaText !== '')
                        <article class="job">
                            <div class="job-head">
                                <div class="job-role">{{ $position !== '' ? $position : 'Role Title' }}</div>
                                @if($metaText !== '')
                                    <div class="job-meta">{{ $metaText }}</div>
                                @endif
                            </div>

                            @if($company !== '')
                                <div class="job-company">{{ $company }}</div>
                            @endif

                            @php $workSummary = (string) data_get($work, 'summary', ''); @endphp
                            @if(trim(strip_tags($workSummary)) !== '')
                                <div class="section-body rich">{!! $workSummary !!}</div>
                            @endif

                            @if(!empty($highs))
                                <ul class="job-highlights">
                                    @foreach($highs as $highlight)
                                        @if(is_string($highlight) && trim($highlight) !== '')
                                            <li>{{ $highlight }}</li>
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

    @if(trim(strip_tags($skillsHtml)) !== '')
        <section class="section" style="{{ $sectionOrderStyle('skills') }}">
            <h2 class="section-title">Technical Skills</h2>
            <div class="section-rule"></div>
            <div class="section-body rich">{!! $skillsHtml !!}</div>
        </section>
    @endif

    @if(!empty($eduItems))
        <section class="section" style="{{ $sectionOrderStyle('education') }}">
            <h2 class="section-title">Education</h2>
            <div class="section-rule"></div>

            @foreach($eduItems as $edu)
                @if(is_array($edu))
                    @php
                        $institution = $safeText(data_get($edu, 'institution'));
                        $studyType   = $safeText(data_get($edu, 'studyType'));
                        $area        = $safeText(data_get($edu, 'area'));
                        $score       = $safeText(data_get($edu, 'score'));
                        $ecity       = $safeText(data_get($edu, 'location.city'));
                        $eregion     = $safeText(data_get($edu, 'location.region'));
                        $eloc        = $compactLocation($ecity, $eregion);
                        if ($eloc === '') {
                            $eloc = $safeText(data_get($edu, 'location')) ?: $safeText(data_get($edu, 'locationName'));
                        }
                        $range       = $fmtDateRange(data_get($edu, 'startDate'), data_get($edu, 'endDate'));
                        $metaParts   = array_values(array_filter([$eloc, $range], fn($v) => $v !== ''));
                        $metaText    = implode(' | ', $metaParts);
                        $degree      = trim($studyType . ($area !== '' ? ' in ' . $area : ''));
                    @endphp

                    @if($degree !== '' || $institution !== '' || $metaText !== '')
                        <article class="edu-item">
                            <div class="edu-head">
                                <div class="edu-degree">{{ $degree !== '' ? $degree : $institution }}</div>
                                @if($metaText !== '')
                                    <div class="job-meta">{{ $metaText }}</div>
                                @endif
                            </div>
                            @if($institution !== '' && $degree !== '')
                                <div class="edu-school">{{ $institution }}</div>
                            @endif
                            @if($score !== '')
                                <div class="edu-score">GPA: {{ $score }}</div>
                            @endif
                        </article>
                    @endif
                @endif
            @endforeach
        </section>
    @endif

    @if(!empty($jsonProjects) || $hasProjectBody)
        <section class="section" style="{{ $sectionOrderStyle('for_us_candidates') }}">
            <h2 class="section-title">Projects</h2>
            <div class="section-rule"></div>

            @if(!empty($jsonProjects))
                @foreach($jsonProjects as $project)
                    @if(is_array($project))
                        @php
                            $pname = $safeText(data_get($project, 'name'));
                            $purl  = $safeText(data_get($project, 'url'));
                            $pent  = (string) data_get($project, 'description', '');
                            $highs = (array) data_get($project, 'highlights', []);
                            $keywords = (array) data_get($project, 'keywords', []);
                            $keywordText = implode(', ', array_values(array_filter(array_map($safeText, $keywords), fn($v) => $v !== '')));
                        @endphp

                        @if($pname !== '' || trim(strip_tags($pent)) !== '' || !empty($highs))
                            <article class="project-item">
                                <div class="project-head">
                                    <div class="project-title">
                                        {{ $pname !== '' ? $pname : 'Project' }}
                                        @if($keywordText !== '')
                                            <span style="font-size:14px; font-weight:700; color:#6B7280;"> ({{ $keywordText }})</span>
                                        @endif
                                    </div>
                                    @if($purl !== '')
                                        <div class="project-link">{{ $purl }}</div>
                                    @endif
                                </div>

                                @if(trim(strip_tags($pent)) !== '')
                                    <div class="section-body rich">{!! $pent !!}</div>
                                @endif

                                @if(!empty($highs))
                                    <ul class="job-highlights">
                                        @foreach($highs as $highlight)
                                            @if(is_string($highlight) && trim($highlight) !== '')
                                                <li>{{ $highlight }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif
                            </article>
                        @endif
                    @endif
                @endforeach
            @elseif($hasProjectBody)
                <div class="section-body rich">{!! $projectBody !!}</div>
            @endif
        </section>
    @endif
</div>
