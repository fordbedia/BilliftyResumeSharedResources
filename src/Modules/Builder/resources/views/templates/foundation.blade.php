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
    $refItems = (array) data_get($resume, 'references', []);
    if (empty($refItems)) {
        $refItems = (array) data_get($resume, 'reference', []);
    }

    $certificateActive = (bool) data_get($resume, 'certificate.is_active');
    $accomplishmentActive = (bool) data_get($resume, 'accomplishment.is_active');
    $languagesActive = (bool) data_get($resume, 'languages.is_active');

    $certificateBody = (string) data_get($resume, 'certificate.body', '');
    $accomplishmentBody = (string) data_get($resume, 'accomplishment.body', '');
    $sidebarLanguages = (array) data_get($resume, 'languages.languages', []);

    $hasCertificate = $certificateActive && trim(strip_tags($certificateBody)) !== '';
    $hasAccomplishment = $accomplishmentActive && trim(strip_tags($accomplishmentBody)) !== '';
    $hasLanguages = $languagesActive && !empty($sidebarLanguages);
    $hasAdditionalInformationSection = $hasCertificate || $hasAccomplishment || $hasLanguages;

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
        font-family: Calibri, Arial, Helvetica, Tahoma, Verdana, sans-serif;
        font-size: 11pt;
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
        font-size: 24pt;
        line-height: 1.05;
        color: #111827;
        font-weight: 700;
        letter-spacing: 0.1px;
    }

    .title{
        margin-top: 6px;
        font-size: 16pt;
        line-height: 1.1;
        font-weight: 700;
        color: #000000;
    }

    .contact-row{
        margin-top: 12px;
        display: flex;
        flex-wrap: wrap;
        gap: 14px 18px;
        align-items: center;
        color: #6B7280;
        font-size: 11pt;
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
        border-top: 1px solid #000000;
    }

    .section{
        margin-top: 18px;
    }

    .section-title{
        margin: 0;
        font-size: 22pt;
        line-height: 1.08;
        font-weight: 700;
        color: #111827;
    }

    .section-rule{
        margin-top: 7px;
        border-top: 1px solid #000000;
    }

    .section-body{
        margin-top: 8px;
        color: #374151;
        font-size: 11pt;
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
        font-size: 16pt;
        line-height: 1.1;
        color: #111827;
        font-weight: 700;
    }

    .job-meta{
        white-space: nowrap;
        color: #000000;
        font-weight: 700;
        font-size: 11pt;
        margin-top: 2px;
    }

    .job-company{
        margin-top: 2px;
        font-size: 11pt;
        line-height: 1.2;
        color: #000000;
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
        font-size: 16pt;
        line-height: 1.1;
        color: #111827;
        font-weight: 700;
    }

    .edu-school{
        margin-top: 3px;
        color: #374151;
        font-size: 11pt;
        font-weight: 700;
    }

    .edu-score{
        margin-top: 2px;
        color: #6B7280;
        font-size: 11pt;
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
        font-size: 16pt;
        line-height: 1.1;
        color: #111827;
        font-weight: 700;
    }

    .project-link{
        color: #000000;
        font-weight: 700;
        font-size: 11pt;
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
                <a href="mailto:{{ $email }}">{{ $email }}</a>
            </div>
        @endif

        @if($phone !== '')
            <div class="contact-item">
                <span>{{ $phone }}</span>
            </div>
        @endif

        @if($displayLocation !== '')
            <div class="contact-item">
                <span>{{ $displayLocation }}</span>
            </div>
        @endif

        @if($profileUrl !== '')
            <div class="contact-item">
                <a href="{{ $profileUrl }}">{{ $profileUrl }}</a>
            </div>
        @endif

        @if($profileUrl === '' && $url !== '')
            <div class="contact-item">
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
                                            <span style="font-size: 11pt; font-weight:700; color:#6B7280;"> ({{ $keywordText }})</span>
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

    @if($hasAdditionalInformationSection)
        <section class="section" style="{{ $sectionOrderStyle('additional_information') }}">
            <h2 class="section-title">Additional Information</h2>
            <div class="section-rule"></div>

            @if($hasCertificate)
                <div class="section-body">
                    <strong>Certificates</strong>
                    <div class="rich" style="margin-top: 6px;">{!! $certificateBody !!}</div>
                </div>
            @endif

            @if($hasAccomplishment)
                <div class="section-body">
                    <strong>Accomplishments</strong>
                    <div class="rich" style="margin-top: 6px;">{!! $accomplishmentBody !!}</div>
                </div>
            @endif

            @if($hasLanguages)
                <div class="section-body">
                    <strong>Languages</strong>
                    <ul class="job-highlights" style="margin-top: 6px;">
                        @foreach($sidebarLanguages as $lang)
                            @php
                                $languageName = $safeText(data_get($lang, 'language')) ?: $safeText(data_get($lang, 'name'));
                                $languageLevel = $safeText(data_get($lang, 'fluency')) ?: $safeText(data_get($lang, 'level'));
                            @endphp
                            @if($languageName !== '')
                                <li>
                                    {{ $languageName }}@if($languageLevel !== '') ({{ $languageLevel }})@endif
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>
    @endif

    @if(!empty($refItems))
        <section class="section" style="{{ $sectionOrderStyle('references') }}">
            <h2 class="section-title">References</h2>
            <div class="section-rule"></div>

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
                        <article class="project-item">
                            <div class="project-head">
                                <div class="project-title">{{ $refName !== '' ? $refName : 'Reference' }}</div>
                            </div>

                            @if($refTitle !== '' || $refCompany !== '')
                                <div class="job-meta">{{ trim($refTitle . ($refTitle !== '' && $refCompany !== '' ? ', ' : '') . $refCompany) }}</div>
                            @endif
                            @if($refEmail !== '')<div class="job-meta">{{ $refEmail }}</div>@endif
                            @if($refPhone !== '')<div class="job-meta">{{ $refPhone }}</div>@endif
                            @if(trim(strip_tags($refBody)) !== '')
                                <div class="section-body rich">{!! $refBody !!}</div>
                            @endif
                        </article>
                    @endif
                @endif
            @endforeach
        </section>
    @endif
</div>
