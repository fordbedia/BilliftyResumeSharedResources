{{-- shared-resources/src/Modules/Builder/resources/views/templates/basic-two.blade.php --}}
@php
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
    $hasSidebar   = $hasLanguages || $hasWebsites;

    $basics = (array) ($resume['basics'] ?? []);

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
        if ($start !== '' && $end === '') return $start . ' – Present';
        if ($start === '' && $end !== '') return $end;
        return $start . ' – ' . $end;
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

    // Header bits
    $label  = $safeText($basics['label'] ?? '');
    $city   = $safeText(data_get($basics, 'location.city'));
    $region = $safeText(data_get($basics, 'location.region'));
    $email  = $safeText($basics['email'] ?? '');
    $url    = $safeText($basics['url'] ?? '');

    $hasEducation = !empty($resume['education']) && is_array($resume['education']);
    $hasSkills    = !empty($resume['skills']);
    $hasRefs      = !empty($resume['references']) && is_array($resume['references']);
    $hasWork      = !empty($resume['work']) && is_array($resume['work']);
    $hasRightRail = $hasSidebar || $hasEducation || $hasSkills || $hasRefs;

	$previewColorScheme = $previewColorScheme ?? null;
    $colorScheme = $previewColorScheme ?? data_get($resume, 'colorScheme');

    $primaryColor = $colorScheme;

    // NOTE: assumes you have this helper available globally (as you already use it)
    $dynamicTextColor = contrastTextFromHsl($primaryColor);
@endphp

<style>
    * { box-sizing: border-box; }

    body {
        margin: 0;
        padding: 0;
        font-family: Calibri, Arial, Helvetica, Tahoma, Verdana, sans-serif;
        color: #2B2D42;
        background: #ECEDEF;
        font-size: 11pt;
        line-height: 1.5;
    }

    .page-shell {
        background: #FFFFFF;
        border-radius: 8px;
        border: 1px solid #E4E6EC;
        padding: 24px;
        width: 100%;
    }

    .name {
        margin: 0;
        font-size: 24pt;
        line-height: 1;
        color: #2D3160;
        letter-spacing: -0.3px;
    }

    .label {
        margin-top: 5px;
        color: #7D8095;
        font-size: 14pt;
		font-weight: bold;
    }

    .summary {
        margin-top: 12px;
        color: #5C6078;
        max-width: 84%;
        font-size: 11pt;
        line-height: 1.55;
    }

    .summary p { margin: 0 0 6px 0; }

    .contact-line {
        margin-top: 8px;
        color: #80849C;
        font-size: 11pt;
        line-height: 1.4;
        word-break: break-word;
    }

    .contact-line a {
        color: #80849C;
        text-decoration: none;
    }

    .contact-inline {
        margin-top: 8px;
        color: #14181f;
        font-size: 11pt;
        line-height: 1.5;
        word-break: break-word;
    }

    .contact-inline a {
        color: #80849C;
        text-decoration: none;
    }

    .inline-dot {
        color: #A6A9BB;
        margin: 0 5px;
    }

    .socials {
        margin-top: 10px;
        color: #A0A3B5;
        font-size: 11pt;
    }

    .socials span { margin-right: 8px; }

    .divider-space { height: 18px; }

    .section-head {
        font-size: 16pt;
        line-height: 1;
        color: #2D3160;
        margin: 0;
        letter-spacing: -0.2px;
    }

    .section-accent {
        margin-top: 5px;
        width: 44px;
        border-top: 3px solid {{$colorScheme ?? '#FF4A78'}};
    }

    .right-title {
        font-size: 14pt;
        line-height: 1;
        color: #2D3160;
        margin: 0;
    }

    .right-block { padding-bottom: 16px; }

    .timeline {
        border-left: 1px solid #E4E6EC;
        margin-left: 5px;
    }

    .job-item {
        position: relative;
        padding-left: 16px;
        padding-bottom: 14px;
    }

    .job-dot {
        position: absolute;
        left: -4px;
        top: 4px;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #BBC0D4;
    }

    .job-item.current .job-dot { background: {{$colorScheme ?? '#FF4A78'}}; }

    .job-title {
        color: #2F3365;
        font-weight: bold;
        font-size: 11pt;
        line-height: 1.3;
    }

    .job-date {
        float: right;
        color: {{ $colorScheme ?? '#FF4A78' }};
        font-size: 11pt;
        font-weight: bold;
        margin-left: 8px;
    }

    .job-meta {
        color: #9195AA;
        font-size: 11pt;
        margin-top: 1px;
    }

    .job-summary,
    .job-summary p {
        margin: 7px 0 0 0;
        color: #14181f;
        font-size: 11pt;
        line-height: 1.45;
    }

    .job-highlights {
        margin: 6px 0 0 12px;
        padding: 0;
        color: #14181f;
        font-size: 11pt;
        line-height: 1.45;
    }

    .job-highlights li { margin-bottom: 3px; }

    .skill-group {
        margin-top: 9px;
        border-top: 1px solid #F0F1F5;
        padding-top: 8px;
    }

    .skill-text {
        color: #7F8498;
        font-size: 11pt;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.35px;
    }

    .skill-chip {
        display: inline-block;
        margin: 0 8px 4px 0;
        color: #2F3365;
        font-size: 11pt;
        font-weight: bold;
    }

    .edu-item { margin-top: 9px; }

    .edu-school {
        color: #2F3365;
        font-size: 11pt;
        font-weight: bold;
        line-height: 1.35;
    }

    .edu-program {
        color: {{ $colorScheme ?? '#FF4A78' }};
        font-size: 11pt;
        font-weight: bold;
        margin-top: 1px;
    }

    .edu-meta {
        color: #9094A9;
        font-size: 11pt;
        line-height: 1.45;
        margin-top: 2px;
    }

    .project-card {
        margin-top: 10px;
        border: 1px solid #ECEEF3;
        background: #F8F9FC;
        border-radius: 4px;
        padding: 10px;
    }

    .project-title {
        color: #2F3365;
        font-size: 11pt;
        font-weight: bold;
    }

    .project-rich,
    .project-rich p,
    .project-rich li {
        color: #61657F;
        font-size: 11pt;
        line-height: 1.45;
    }

    .project-rich p { margin: 6px 0 4px 0; }
    .project-rich ul { margin: 4px 0 0 12px; padding: 0; }

    .lang-row,
    .web-row,
    .ref-row {
        margin-top: 7px;
        color: #575B73;
        font-size: 11pt;
        line-height: 1.45;
    }

    .lang-meta,
    .web-url {
        color: #9AA0B4;
        margin-left: 6px;
    }

    .ref-quote {
        margin-top: 7px;
        padding: 10px;
        border: 1px solid #F1E6E9;
        background: #FCF7F8;
        border-radius: 4px;
        color: #8A8EA2;
        font-size: 11pt;
        line-height: 1.45;
    }

    .foot {
        margin-top: 14px;
        border-top: 1px solid #F0F1F5;
        padding-top: 10px;
        color: #A5A9BA;
        font-size: 11pt;
    }

    .rich-inline p { margin: 0 0 5px 0; }
    .rich-inline ul { margin: 4px 0 0 12px; padding: 0; }

    .header-flex {
        display: flex;
        align-items: flex-start;
    }
	.header-full {
		width: 100%;
	}
    .header-left {
        width: 62%;
        padding-right: 14px;
    }

    .header-right {
        width: 38%;
    }

    .content-flex {
        display: flex;
        align-items: flex-start;
    }

    .main-col {
        width: 100%;
    }

    .main-col.with-right {
        width: 63%;
        padding-right: 14px;
    }

    .side-col {
        width: 37%;
    }
	.height-separator {
		height: 10px;
	}
</style>

<div class="page-shell">
    <div class="header-flex">
        <div class="header-full" style="{{ $sectionOrderStyle('basics') }}">
                <h1 class="name">{{ $basics['name'] ?? 'Your Name' }}</h1>

                @if($label !== '')
                    <div class="label">{{ $label }}</div>
                @endif

                @php
                    $locationInline = '';
                    if ($city !== '' || $region !== '') {
                        $locationInline = $city !== '' && $region !== '' ? ($city . ', ' . $region) : ($city !== '' ? $city : $region);
                    }
                    $phoneInline = !empty($basics['phone']) ? $safeText($basics['phone']) : '';
                    $summaryInline = trim(strip_tags((string) data_get($basics, 'summary', ''))) !== ''
                        ? trim(preg_replace('/\\s+/u', ' ', strip_tags((string) data_get($basics, 'summary', ''))))
                        : '';
                @endphp


                <div class="contact-inline">
                    @php $printed = false; @endphp

                    @if($email !== '')
                        {{ $email }}
                        @php $printed = true; @endphp
                    @endif

                    @if($locationInline !== '')
                        @if($printed)<span class="inline-dot">•</span>@endif
                        {{ $locationInline }}
                        @php $printed = true; @endphp
                    @endif

                    @if($url !== '')
                        @if($printed)<span class="inline-dot">•</span>@endif
                        <a href="{{ $url }}">{{ $url }}</a>
                        @php $printed = true; @endphp
                    @endif

                    @if($phoneInline !== '')
                        @if($printed)<span class="inline-dot">•</span>@endif
                        {{ $phoneInline }}
                        @php $printed = true; @endphp
                    @endif

					<div class="height-separator"></div>

                    @if($summaryInline !== '')
                        {{ $summaryInline }}
                    @endif
                </div>

                <div class="socials">
                    @if(!empty($basics['profiles']) && is_array($basics['profiles']))
                        @foreach($basics['profiles'] as $profile)
                            @php $net = $safeText(data_get($profile, 'network')); @endphp
                            @if($net !== '') <span>{{ $net }}</span> @endif
                        @endforeach
                    @endif
                </div>
        </div>

{{--        <div class="header-right">--}}
{{--                @if($email !== '')--}}
{{--                    <div class="contact-line">{{ $email }}</div>--}}
{{--                @endif--}}

{{--                @if($city !== '' || $region !== '')--}}
{{--                    <div class="contact-line">--}}
{{--                        @if($city !== ''){{ $city }}@endif--}}
{{--                        @if($region !== '')@if($city !== ''), @endif{{ $region }}@endif--}}
{{--                    </div>--}}
{{--                @endif--}}

{{--                @if($url !== '')--}}
{{--                    <div class="contact-line"><a href="{{ $url }}">{{ $url }}</a></div>--}}
{{--                @endif--}}

{{--                @if(!empty($basics['phone']))--}}
{{--                    <div class="contact-line">{{ $safeText($basics['phone']) }}</div>--}}
{{--                @endif--}}
{{--        </div>--}}
    </div>

    <div class="divider-space"></div>

    <div class="content-flex">
        <div class="main-col {{ $hasRightRail ? 'with-right' : '' }}" style="display: flex; flex-direction: column;">
                @if($hasWork)
                    <div style="{{ $sectionOrderStyle('work') }}">
                    <h2 class="section-head">Experience</h2>
                    <div class="section-accent"></div>

                    <div style="height: 7px;"></div>

                    <div class="timeline">
                        @foreach($resume['work'] as $index => $work)
                            @if(is_array($work))
                                @php
                                    $position = $safeText(data_get($work, 'position'));
                                    $company  = $safeText(data_get($work, 'name'));
                                    $range    = $fmtDateRange(data_get($work, 'startDate'), data_get($work, 'endDate'));
                                    $workLocation = $safeText(data_get($work, 'location'));
                                    $summary  = (string) data_get($work, 'summary', '');
                                    $highs    = (array) data_get($work, 'highlights', []);

                                    $hasHighs = false;
                                    foreach ($highs as $h) {
                                        if (is_string($h) && trim($h) !== '') { $hasHighs = true; break; }
                                    }

                                    $showItem = $position !== '' || $company !== '' || $range !== '' || $workLocation !== '' || trim(strip_tags($summary)) !== '' || $hasHighs;
                                @endphp

                                @if($showItem)
                                    <div class="job-item {{ $index === 0 ? 'current' : '' }}">
                                        <span class="job-dot"></span>

                                        @if($range !== '')
                                            <span class="job-date">{{ $range }}</span>
                                        @endif

                                        <div class="job-title">
                                            {{ $position !== '' ? $position : 'Role' }}
                                        </div>

                                        @if($company !== '' || $workLocation !== '')
                                            <div class="job-meta">
                                                @if($company !== ''){{ $company }}@endif
                                                @if($workLocation !== '') @if($company !== '') • @endif{{ $workLocation }}@endif
                                            </div>
                                        @endif

                                        @if(trim(strip_tags($summary)) !== '')
                                            <div class="job-summary rich-inline">{!! $summary !!}</div>
                                        @endif

                                        @if($hasHighs)
                                            <ul class="job-highlights">
                                                @foreach($highs as $h)
                                                    @if(is_string($h) && trim($h) !== '')
                                                        <li>{{ $h }}</li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    </div>
                    </div>
                @endif

                @if($hasProject)
                    <div style="{{ $sectionOrderStyle('for_us_candidates') }}">
                    <div style="height: 8px;"></div>
                    <h2 class="section-head">Projects</h2>
                    <div class="section-accent"></div>

                    <div class="project-card">
                        <div class="project-title">Selected Projects</div>
                        <div class="project-rich rich-inline">{!! $projectBody !!}</div>
                    </div>
                    </div>
                @endif

                @if($hasAccomplishment)
                    <div class="project-card" style="{{ $sectionOrderStyle('additional_information') }}">
                        <div class="project-title">Accomplishments</div>
                        <div class="project-rich rich-inline">{!! $accomplishmentBody !!}</div>
                    </div>
                @endif

                @if($hasVolunteer)
                    <div class="project-card" style="{{ $sectionOrderStyle('for_us_candidates') }}">
                        <div class="project-title">Volunteer</div>
                        <div class="project-rich rich-inline">{!! $volunteerBody !!}</div>
                    </div>
                @endif

                @if($hasAffiliation)
                    <div class="project-card" style="{{ $sectionOrderStyle('for_us_candidates') }}">
                        <div class="project-title">Affiliations</div>
                        <div class="project-rich rich-inline">{!! $affiliationBody !!}</div>
                    </div>
                @endif

                @if($hasCertificate)
                    <div class="project-card" style="{{ $sectionOrderStyle('additional_information') }}">
                        <div class="project-title">Certifications</div>
                        <div class="project-rich rich-inline">{!! $certificateBody !!}</div>
                    </div>
                @endif

                @if($hasInterest)
                    <div class="project-card" style="{{ $sectionOrderStyle('for_us_candidates') }}">
                        <div class="project-title">Interests</div>
                        <div class="project-rich rich-inline">{!! $interestBody !!}</div>
                    </div>
                @endif
        </div>

        @if($hasRightRail)
                <div class="side-col" style="display: flex; flex-direction: column;">
                    @if($hasEducation)
                        <div class="right-block" style="{{ $sectionOrderStyle('education') }}">
                            <h3 class="right-title">Education</h3>
                            <div class="section-accent"></div>

                            @foreach($resume['education'] as $edu)
                                @if(is_array($edu))
                                    @php
                                        $institution = $safeText(data_get($edu, 'institution'));
                                        $studyType   = $safeText(data_get($edu, 'studyType'));
                                        $area        = $safeText(data_get($edu, 'area'));
                                        $score       = $safeText(data_get($edu, 'score'));
                                        $range       = $fmtDateRange(data_get($edu, 'startDate'), data_get($edu, 'endDate'));
                                    @endphp

                                    @if($institution !== '' || $studyType !== '' || $area !== '' || $range !== '' || $score !== '')
                                        <div class="edu-item">
                                            @if($institution !== '')
                                                <div class="edu-school">{{ $institution }}</div>
                                            @endif
                                            @if(trim($studyType . ' ' . $area) !== '')
                                                <div class="edu-program">{{ trim($studyType . ' ' . $area) }}</div>
                                            @endif
                                            @if($range !== '')
                                                <div class="edu-meta">{{ $range }}</div>
                                            @endif
                                            @if($score !== '')
                                                <div class="edu-meta">Score: {{ $score }}</div>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if($hasSkills)
                        <div class="right-block" style="{{ $sectionOrderStyle('skills') }}">
                            <h3 class="right-title">Skills</h3>
                            <div class="section-accent"></div>

                            <div class="skill-group">
                                {!! $resume['skills']['body'] !!}
                            </div>
                        </div>
                    @endif

                    @if($hasLanguages)
                        <div class="right-block" style="{{ $sectionOrderStyle('additional_information') }}">
                            <h3 class="right-title">Languages</h3>
                            <div class="section-accent"></div>

                            @foreach($sidebarLanguages as $lang)
                                @php
                                    $l = $normalizeLanguage($lang);
                                @endphp

                                @if($l['name'] !== '')
                                    <div class="lang-row">
                                        <strong>{{ $l['name'] }}</strong>
                                        @if($l['meta'] !== '')
                                            <span class="lang-meta">{{ $l['meta'] }}</span>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if($hasWebsites)
                        <div class="right-block" style="{{ $sectionOrderStyle('for_us_candidates') }}">
                            <h3 class="right-title">Websites</h3>
                            <div class="section-accent"></div>

                            @foreach($websites as $site)
                                @php $w = $normalizeWebsite($site); @endphp

                                @if($w['url'] !== '')
                                    <div class="web-row">
                                        <span class="web-url"><a href="{{ $w['url'] }}">{{ $w['url'] }}</a></span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if($hasRefs)
                        <div class="right-block" style="{{ $sectionOrderStyle('references') }}">
                            <h3 class="right-title">References</h3>
                            <div class="section-accent"></div>

                            @foreach($resume['references'] as $r)
                                @if(is_array($r))
                                    @php
                                        $refName = $safeText(data_get($r, 'name'));
                                        $refBody = (string) data_get($r, 'reference', '');
                                    @endphp

                                    @if($refName !== '' || trim(strip_tags($refBody)) !== '')
                                        <div class="ref-row">
                                            @if($refName !== '')<strong>{{ $refName }}</strong>@endif
                                            @if(trim(strip_tags($refBody)) !== '')
                                                <div class="ref-quote rich-inline">{!! $refBody !!}</div>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
        @endif
    </div>

    <div class="foot">Generated from JSON Resume Standard</div>
</div>
