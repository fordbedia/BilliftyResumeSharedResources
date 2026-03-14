{{-- shared-resources/src/Modules/Builder/resources/views/templates/executive.blade.php --}}

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

    $isListArray = function (array $arr) {
        $i = 0;
        foreach (array_keys($arr) as $key) {
            if ($key !== $i++) return false;
        }
        return true;
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

    $name  = $safeText(data_get($basics, 'name')) ?: 'Your Name';
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

    $skillsNode = data_get($resume, 'skills', []);
    $skillItems = [];
    if (is_array($skillsNode)) {
        $nestedSkills = data_get($skillsNode, 'skills', []);
        $nestedItems = data_get($skillsNode, 'items', []);
        if (is_array($nestedSkills) && !empty($nestedSkills)) {
            $skillItems = $nestedSkills;
        } elseif (is_array($nestedItems) && !empty($nestedItems)) {
            $skillItems = $nestedItems;
        } elseif ($isListArray($skillsNode)) {
            $skillItems = $skillsNode;
        }
    }

    $competencies = [];
    foreach ($skillItems as $skill) {
        if (!is_array($skill)) continue;

        $title = $safeText(data_get($skill, 'name'))
            ?: $safeText(data_get($skill, 'title'))
            ?: $safeText(data_get($skill, 'category'))
            ?: $safeText(data_get($skill, 'skill'));

        $keywords = (array) data_get($skill, 'keywords', []);
        $keywordText = implode(', ', array_values(array_filter(array_map($safeText, $keywords), fn($v) => $v !== '')));
        $levelText = $safeText(data_get($skill, 'level'));
        $text = $safeText(data_get($skill, 'description')) ?: $safeText(data_get($skill, 'summary'));
        if ($text === '') {
            $parts = array_values(array_filter([$keywordText, $levelText], fn($v) => $v !== ''));
            $text = implode(', ', $parts);
        }

        if ($title !== '' || $text !== '') {
            $competencies[] = ['title' => $title, 'text' => $text];
        }
    }

    $competencyColumns = [[], []];
    if (!empty($competencies)) {
        $splitAt = (int) ceil(count($competencies) / 2);
        $competencyColumns = [
            array_slice($competencies, 0, $splitAt),
            array_slice($competencies, $splitAt),
        ];
    }
?>

<style>
    @page { margin: 0; }

    html, body {
        margin: 0;
        padding: 0;
        width: 100%;
    }

    * { box-sizing: border-box; }

    body {
        font-family: Calibri, Arial, Helvetica, Tahoma, Verdana, sans-serif;
        font-size: 11pt;
        line-height: 1.45;
        color: #2F2F2F;
        background: #E5E7EB;
    }

    .sheet {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        background: #FFFFFF;
        padding: 15mm 14mm 14mm;
        display: flex;
        flex-direction: column;
    }

    .header {
        text-align: center;
    }

    .name {
        margin: 0;
        font-family: Georgia, "Times New Roman", Garamond, serif;
        font-size: 24pt;
        line-height: 1.05;
        font-weight: 700;
        color: #111111;
        letter-spacing: 0.15px;
    }

    .title {
        margin-top: 6px;
        font-size: 15pt;
        line-height: 1.2;
        color: #666666;
        font-weight: 400;
        letter-spacing: 0.08em;
    }

    .contact {
        margin-top: 10px;
        font-size: 11pt;
        color: #666666;
    }

    .contact-row {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        gap: 3px 16px;
        margin-top: 2px;
    }

    .contact-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
        color: #666666;
    }

    .contact-item a {
        color: #666666;
        text-decoration: none;
        word-break: break-word;
    }

    .contact-icon {
        width: 10px;
        height: 10px;
        flex: 0 0 auto;
        fill: #888888;
    }

    .section {
        margin-top: 14px;
        border-top: 1px solid #D9D9D9;
        padding-top: 12px;
    }

    .section-title {
        margin: 0 0 8px 0;
        text-align: center;
        font-family: Georgia, "Times New Roman", Garamond, serif;
        font-size: 15pt;
        line-height: 1.15;
        font-weight: 700;
        color: #1F1F1F;
    }

    .profile-body {
        font-size: 11pt;
        line-height: 1.52;
        color: #363636;
    }

    .profile-body p {
        margin: 0 0 6px 0;
    }

    .entry {
        margin-top: 14px;
    }

    .entry:first-child {
        margin-top: 0;
    }

    .entry-head {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 10px;
    }

    .entry-title {
        margin: 0;
        font-size: 12pt;
        line-height: 1.2;
        color: #1A1A1A;
        font-weight: 700;
    }

    .entry-date {
        white-space: nowrap;
        font-size: 11pt;
        line-height: 1.2;
        color: #666666;
        font-weight: 400;
        font-style: italic;
    }

    .entry-sub {
        margin-top: 2px;
        font-size: 11pt;
        line-height: 1.3;
        color: #3F3F3F;
        font-weight: 700;
    }

    .entry-score {
        margin-top: 2px;
        font-size: 10pt;
        line-height: 1.3;
        color: #6F6F6F;
    }

    .entry-summary {
        margin-top: 5px;
        font-size: 11pt;
        color: #363636;
    }

    .entry-summary p {
        margin: 0 0 5px 0;
    }

    .bullets {
        margin: 6px 0 0 0;
        padding-left: 16px;
        font-size: 11pt;
        line-height: 1.48;
        color: #363636;
    }

    .bullets li {
        margin: 0 0 5px 0;
    }

    .competency-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        margin-top: 6px;
    }

    .competency-item {
        margin-bottom: 8px;
    }

    .competency-label {
        margin: 0 0 4px 0;
        font-size: 11pt;
        line-height: 1.3;
        color: #1F1F1F;
        font-weight: 700;
    }

    .competency-text {
        margin: 0;
        font-size: 11pt;
        line-height: 1.48;
        color: #363636;
    }

    .plain-rich p {
        margin: 0 0 6px 0;
    }

    .plain-rich ul, .plain-rich ol {
        margin: 6px 0 0 16px;
        padding: 0;
    }

    .plain-rich li {
        margin: 0 0 4px 0;
    }
</style>

<div class="sheet">
    <header class="header" style="{{ $sectionOrderStyle('basics') }}">
        <h1 class="name">{{ $name }}</h1>

        @if($label !== '')
            <div class="title">{{ $label }}</div>
        @endif

        @if($displayLocation !== '' || $email !== '' || $phone !== '' || $profileUrl !== '' || $url !== '')
            <div class="contact">
                <div class="contact-row">
                    @if($displayLocation !== '')
                        <div class="contact-item">
                            <svg class="contact-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 2.5a7.5 7.5 0 0 0-7.5 7.5c0 5.26 6.64 11.03 6.92 11.27a.9.9 0 0 0 1.16 0c.28-.24 6.92-6.01 6.92-11.27A7.5 7.5 0 0 0 12 2.5Zm0 10.35a2.85 2.85 0 1 1 0-5.7 2.85 2.85 0 0 1 0 5.7Z"/>
                            </svg>
                            <span>{{ $displayLocation }}</span>
                        </div>
                    @endif

                    @if($email !== '')
                        <div class="contact-item">
                            <svg class="contact-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3.75 5.25h16.5A1.5 1.5 0 0 1 21.75 6.75v10.5a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5V6.75a1.5 1.5 0 0 1 1.5-1.5Zm0 1.8 8.25 5.7 8.25-5.7"/>
                            </svg>
                            <a href="mailto:{{ $email }}">{{ $email }}</a>
                        </div>
                    @endif

                    @if($phone !== '')
                        <div class="contact-item">
                            <svg class="contact-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M7.9 2.8c.34-.82 1.26-1.23 2.09-.92l2.1.79a1.5 1.5 0 0 1 .88 1.93l-.86 2.3a1.5 1.5 0 0 1-1.72.94l-1.5-.32a13.4 13.4 0 0 0 7.57 7.57l-.32-1.5a1.5 1.5 0 0 1 .94-1.72l2.3-.86a1.5 1.5 0 0 1 1.93.88l.79 2.1a1.6 1.6 0 0 1-.92 2.09l-1.95.78a3.3 3.3 0 0 1-3.14-.33A20.5 20.5 0 0 1 6.45 7.89a3.3 3.3 0 0 1-.33-3.14l.78-1.95Z"/>
                            </svg>
                            <span>{{ $phone }}</span>
                        </div>
                    @endif
                </div>

                @if($profileUrl !== '' || ($profileUrl === '' && $url !== ''))
                    <div class="contact-row">
                        <div class="contact-item">
                            <svg class="contact-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M20.5 2H3.5A1.5 1.5 0 0 0 2 3.5v17A1.5 1.5 0 0 0 3.5 22h17a1.5 1.5 0 0 0 1.5-1.5v-17A1.5 1.5 0 0 0 20.5 2ZM8.1 18.4H5.5V9.9h2.6v8.5ZM6.8 8.7a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3Zm11.6 9.7h-2.6v-4.2c0-1-.02-2.3-1.4-2.3-1.4 0-1.6 1.1-1.6 2.2v4.3h-2.6V9.9h2.5v1.2h.04c.35-.66 1.2-1.35 2.47-1.35 2.65 0 3.14 1.74 3.14 4v4.65Z"/>
                            </svg>
                            @if($profileUrl !== '')
                                <a href="{{ $profileUrl }}">{{ $profileUrl }}</a>
                            @else
                                <a href="{{ $url }}">{{ $url }}</a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </header>

    @if(trim(strip_tags($summaryHtml)) !== '')
        <section class="section" style="{{ $sectionOrderStyle('basics') }}">
            <h2 class="section-title">Profile</h2>
            <div class="profile-body plain-rich">{!! $summaryHtml !!}</div>
        </section>
    @endif

    @if(!empty($workItems))
        <section class="section" style="{{ $sectionOrderStyle('work') }}">
            <h2 class="section-title">Experience</h2>

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
                        $range = str_replace(' - ', ' — ', $range);
                        $metaParts = array_values(array_filter([$company, $wloc], fn($v) => $v !== ''));
                        $metaText = implode(' | ', $metaParts);
                        $highs = (array) data_get($work, 'highlights', []);
                    @endphp

                    @if($position !== '' || $metaText !== '' || $range !== '')
                        <article class="entry">
                            <div class="entry-head">
                                <h3 class="entry-title">{{ $position !== '' ? $position : 'Role Title' }}</h3>
                                @if($range !== '')
                                    <div class="entry-date">{{ $range }}</div>
                                @endif
                            </div>

                            @if($metaText !== '')
                                <div class="entry-sub">{{ $metaText }}</div>
                            @endif

                            @php $workSummary = (string) data_get($work, 'summary', ''); @endphp
                            @if(trim(strip_tags($workSummary)) !== '')
                                <div class="entry-summary plain-rich">{!! $workSummary !!}</div>
                            @endif

                            @if(!empty($highs))
                                <ul class="bullets">
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

    @if(!empty($eduItems))
        <section class="section" style="{{ $sectionOrderStyle('education') }}">
            <h2 class="section-title">Education</h2>

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
                        $range       = str_replace(' - ', ' — ', $range);
                        $metaText    = implode(' | ', array_values(array_filter([$institution, $eloc], fn($v) => $v !== '')));
                        $degree      = trim($studyType . ($area !== '' ? ' in ' . $area : ''));
                    @endphp

                    @if($degree !== '' || $metaText !== '' || $range !== '' || $score !== '')
                        <article class="entry">
                            <div class="entry-head">
                                <h3 class="entry-title">{{ $degree !== '' ? $degree : ($institution !== '' ? $institution : 'Education') }}</h3>
                                @if($range !== '')
                                    <div class="entry-date">{{ $range }}</div>
                                @endif
                            </div>
                            @if($metaText !== '')
                                <div class="entry-sub" style="font-weight: 400;">{{ $metaText }}</div>
                            @endif
                            @if($score !== '')
                                <div class="entry-score">{{ $score }}</div>
                            @endif
                        </article>
                    @endif
                @endif
            @endforeach
        </section>
    @endif

    @if(!empty($competencies) || trim(strip_tags($skillsHtml)) !== '')
        <section class="section" style="{{ $sectionOrderStyle('skills') }}">
            <h2 class="section-title">Competencies</h2>

            @if(!empty($competencies))
                <div class="competency-grid">
                    <div>
                        @foreach($competencyColumns[0] as $comp)
                            <article class="competency-item">
                                @if($comp['title'] !== '')
                                    <h3 class="competency-label">{{ $comp['title'] }}</h3>
                                @endif
                                @if($comp['text'] !== '')
                                    <p class="competency-text">{{ $comp['text'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                    <div>
                        @foreach($competencyColumns[1] as $comp)
                            <article class="competency-item">
                                @if($comp['title'] !== '')
                                    <h3 class="competency-label">{{ $comp['title'] }}</h3>
                                @endif
                                @if($comp['text'] !== '')
                                    <p class="competency-text">{{ $comp['text'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="plain-rich" style="font-size: 11pt; color: #363636;">{!! $skillsHtml !!}</div>
            @endif
        </section>
    @endif

    @if(!empty($jsonProjects) || $hasProjectBody)
        <section class="section" style="{{ $sectionOrderStyle('for_us_candidates') }}">
            <h2 class="section-title">Projects</h2>

            @if(!empty($jsonProjects))
                @foreach($jsonProjects as $project)
                    @if(is_array($project))
                        @php
                            $pname = $safeText(data_get($project, 'name'));
                            $purl  = $safeText(data_get($project, 'url'));
                            $pent  = (string) data_get($project, 'description', '');
                            $highs = (array) data_get($project, 'highlights', []);
                        @endphp

                        @if($pname !== '' || trim(strip_tags($pent)) !== '' || !empty($highs))
                            <article class="entry">
                                <div class="entry-head">
                                    <h3 class="entry-title">{{ $pname !== '' ? $pname : 'Project' }}</h3>
                                    @if($purl !== '')
                                        <div class="entry-date" style="font-style: normal;">{{ $purl }}</div>
                                    @endif
                                </div>

                                @if(trim(strip_tags($pent)) !== '')
                                    <div class="entry-summary plain-rich">{!! $pent !!}</div>
                                @endif

                                @if(!empty($highs))
                                    <ul class="bullets">
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
                <div class="plain-rich" style="font-size: 11pt; color: #363636;">{!! $projectBody !!}</div>
            @endif
        </section>
    @endif

    @if($hasAdditionalInformationSection)
        <section class="section" style="{{ $sectionOrderStyle('additional_information') }}">
            <h2 class="section-title">Additional Information</h2>

            @if($hasCertificate)
                <div class="entry">
                    <h3 class="competency-label">Certificates</h3>
                    <div class="plain-rich" style="font-size: 11pt; color: #363636;">{!! $certificateBody !!}</div>
                </div>
            @endif

            @if($hasAccomplishment)
                <div class="entry">
                    <h3 class="competency-label">Accomplishments</h3>
                    <div class="plain-rich" style="font-size: 11pt; color: #363636;">{!! $accomplishmentBody !!}</div>
                </div>
            @endif

            @if($hasLanguages)
                <div class="entry">
                    <h3 class="competency-label">Languages</h3>
                    <ul class="bullets">
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
                        <article class="entry">
                            <h3 class="entry-title">{{ $refName !== '' ? $refName : 'Reference' }}</h3>

                            @if($refTitle !== '' || $refCompany !== '')
                                <div class="entry-sub" style="font-weight:400;">
                                    {{ trim($refTitle . ($refTitle !== '' && $refCompany !== '' ? ', ' : '') . $refCompany) }}
                                </div>
                            @endif
                            @if($refEmail !== '')<div class="entry-score">{{ $refEmail }}</div>@endif
                            @if($refPhone !== '')<div class="entry-score">{{ $refPhone }}</div>@endif
                            @if(trim(strip_tags($refBody)) !== '')
                                <div class="entry-summary plain-rich">{!! $refBody !!}</div>
                            @endif
                        </article>
                    @endif
                @endif
            @endforeach
        </section>
    @endif
</div>
