{{-- shared-resources/src/Modules/Builder/resources/views/templates/basic.blade.php --}}
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

    // Header bits
    $name   = $safeText($basics['name'] ?? '');
    $label  = $safeText($basics['label'] ?? '');
    $city   = $safeText(data_get($basics, 'location.city'));
    $region = $safeText(data_get($basics, 'location.region'));
    $email  = $safeText($basics['email'] ?? '');
    $phone  = $safeText($basics['phone'] ?? '');
    $url    = $safeText($basics['url'] ?? '');

    $locationInline = '';
    if ($city !== '' || $region !== '') {
        $locationInline = $city !== '' && $region !== '' ? ($city . ', ' . $region) : ($city !== '' ? $city : $region);
    }

    $hasEducation = !empty($resume['education']) && is_array($resume['education']);
    $hasRefs      = !empty($resume['references']) && is_array($resume['references']);
    $hasWork      = !empty($resume['work']) && is_array($resume['work']);

    $skillsBody = '';
    if (is_array($resume['skills'] ?? null)) {
        $skillsBody = $safeText(data_get($resume, 'skills.body'));
        if ($skillsBody === '' && isset($resume['skills'][0]) && is_array($resume['skills'][0])) {
            $skillsBody = $safeText(data_get($resume, 'skills.0.body'));
        }
    }

    $skillItems = [];
    if ($skillsBody === '' && is_array($resume['skills'] ?? null)) {
        foreach ((array) $resume['skills'] as $skill) {
            if (is_string($skill) && trim($skill) !== '') {
                $skillItems[] = trim($skill);
                continue;
            }

            if (!is_array($skill)) {
                continue;
            }

            $skillName = $safeText(data_get($skill, 'name'));
            $skillLevel = $safeText(data_get($skill, 'level'));
            if ($skillName === '') {
                continue;
            }

            $skillItems[] = $skillLevel !== '' ? ($skillName . ' (' . $skillLevel . ')') : $skillName;
        }
    }

    $hasSkills    = ($skillsBody !== '') || !empty($skillItems);
    $hasRightRail = $hasSidebar || $hasEducation || $hasSkills || $hasRefs;

    $previewColorScheme = $previewColorScheme ?? null;
    $colorScheme = $previewColorScheme ?? data_get($resume, 'colorScheme', '#111827');
@endphp

<style>
    @page { margin: 14mm; }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 11.2px;
        line-height: 1.42;
        color: #111;
        background: #fff;
    }

    .container { width: 100%; }
    .muted { color: #666; }
    .tiny { font-size: 10px; }
    .sp-6 { height: 6px; }
    .sp-10 { height: 10px; }
    .sp-14 { height: 14px; }

    h1 {
        font-size: 24px;
        line-height: 1.15;
        margin: 0;
        letter-spacing: 0.2px;
        font-weight: 700;
        color: #111;
    }

    h2 {
        font-size: 14px;
        margin: 0;
        padding: 0 0 5px 0;
        letter-spacing: 1px;
        text-transform: uppercase;
        border-bottom: 1px solid #e9e9e9;
    }

    .section { padding-top: 13px; }
    .row { padding-top: 6px; }
    .avoid-break { page-break-inside: avoid; break-inside: avoid; }

    .contact-line {
        color: #666;
        font-size: 14px;
        line-height: 1.45;
        word-break: break-word;
    }

    .contact-line a {
        color: #111;
        text-decoration: none;
        word-break: break-word;
    }

    .inline-dot {
        color: #aaa;
        margin: 0 5px;
    }

    .pill {
        display: inline-block;
        border: 1px solid #e4e4e4;
        padding: 3px 7px;
        border-radius: 999px;
        margin: 0 6px 6px 0;
        font-size: 14px;
        color: #222;
        background: #fafafa;
        vertical-align: top;
        max-width: 100%;
        word-wrap: break-word;
    }

    .divider { border-top: 1px solid #eee; margin: 10px 0; }

    .rich { color: #1c1c1c; }
    .rich p { margin: 0 0 6px 0; }
    .rich ul { margin: 6px 0 0 16px; padding: 0; }
    .rich ol { margin: 6px 0 0 16px; padding: 0; }
    .rich li { margin: 0 0 4px 0; }

    ul.clean { margin: 6px 0 0 16px; padding: 0; }
    ul.clean li { margin: 0 0 4px 0; }

    .meta-line {
        color: #666;
        font-size: 14px;
        line-height: 1.45;
        margin-top: 2px;
    }

    .sidebar-card {
        border: 1px solid #efefef;
        padding: 10px 10px 8px 10px;
        border-radius: 6px;
        background: #fcfcfc;
    }

    .sidebar-title {
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin: 0 0 6px 0;
        padding: 0 0 6px 0;
        border-bottom: 1px solid #eee;
    }

    .sidebar-item { padding-top: 6px; }

    .accent {
        color: {{ $colorScheme }};
    }

    a {
        color: #111;
        text-decoration: none;
        word-break: break-word;
    }
</style>

<div class="container">
    {{-- Header --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
        <tr>
            <td valign="top">
                <h1>{{ $name !== '' ? $name : 'Your Name' }}</h1>

                @if($label !== '')
                    <div class="row muted">{{ $label }}</div>
                @endif

                @if($email !== '' || $phone !== '' || $locationInline !== '' || $url !== '')
                    <div class="row contact-line">
                        @php $printed = false; @endphp

                        @if($email !== '')
                            {{ $email }}
                            @php $printed = true; @endphp
                        @endif

                        @if($phone !== '')
                            @if($printed)<span class="inline-dot">-</span>@endif
                            {{ $phone }}
                            @php $printed = true; @endphp
                        @endif

                        @if($locationInline !== '')
                            @if($printed)<span class="inline-dot">-</span>@endif
                            {{ $locationInline }}
                            @php $printed = true; @endphp
                        @endif

                        @if($url !== '')
                            @if($printed)<span class="inline-dot">-</span>@endif
                            <a href="{{ $url }}">{{ $url }}</a>
                        @endif
                    </div>
                @endif

                @if(!empty($basics['profiles']) && is_array($basics['profiles']))
                    <div class="row">
                        @foreach($basics['profiles'] as $profile)
                            @php
                                $network = $safeText(data_get($profile, 'network'));
                                $value = $safeText(data_get($profile, 'url')) ?: $safeText(data_get($profile, 'username'));
                            @endphp
                            @if($network !== '' || $value !== '')
                                <span class="pill">{{ $network !== '' ? $network : 'Profile' }}@if($value !== ''): {{ $value }}@endif</span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </td>
        </tr>
    </table>

    @if(trim(strip_tags((string) data_get($basics, 'summary', ''))) !== '')
        <div class="sp-10"></div>
        <div class="rich">{!! data_get($basics, 'summary', '') !!}</div>
    @endif

    <div class="sp-14"></div>

    {{-- Main layout --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
        <tr>
            <td valign="top" style="{{ $hasRightRail ? 'width: 66%; padding-right: 14px;' : 'width: 100%;' }}">
                @if($hasWork)
                    <div class="section">
                        <h2>Experience</h2>

                        @foreach($resume['work'] as $work)
                            @if(is_array($work))
                                @php
                                    $position = $safeText(data_get($work, 'position'));
                                    $company  = $safeText(data_get($work, 'name'));
                                    $range    = $fmtDateRange(data_get($work, 'startDate'), data_get($work, 'endDate'));
                                    $summary  = (string) data_get($work, 'summary', '');
                                    $highs    = (array) data_get($work, 'highlights', []);
                                @endphp

                                @if($position !== '' || $company !== '' || $range !== '' || trim(strip_tags($summary)) !== '' || !empty($highs))
                                    <div class="row avoid-break">
                                        <strong>{{ $position !== '' ? $position : 'Role' }}</strong>
                                        @if($company !== '') - {{ $company }} @endif

                                        @if($range !== '')
                                            <div class="meta-line accent">{{ $range }}</div>
                                        @endif

                                        @if(trim(strip_tags($summary)) !== '')
                                            <div class="rich">{!! $summary !!}</div>
                                        @endif

                                        @if(!empty($highs))
                                            <ul class="clean">
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
                @endif

                @if($hasProject)
                    <div class="section">
                        <h2>Projects</h2>
                        <div class="row rich">{!! $projectBody !!}</div>
                    </div>
                @endif

                @if($hasAccomplishment)
                    <div class="section">
                        <h2>Accomplishments</h2>
                        <div class="row rich">{!! $accomplishmentBody !!}</div>
                    </div>
                @endif

                @if($hasVolunteer)
                    <div class="section">
                        <h2>Volunteer</h2>
                        <div class="row rich">{!! $volunteerBody !!}</div>
                    </div>
                @endif

                @if($hasAffiliation)
                    <div class="section">
                        <h2>Affiliations</h2>
                        <div class="row rich">{!! $affiliationBody !!}</div>
                    </div>
                @endif

                @if($hasCertificate)
                    <div class="section">
                        <h2>Certifications</h2>
                        <div class="row rich">{!! $certificateBody !!}</div>
                    </div>
                @endif

                @if($hasInterest)
                    <div class="section">
                        <h2>Interests</h2>
                        <div class="row rich">{!! $interestBody !!}</div>
                    </div>
                @endif
            </td>

            @if($hasRightRail)
                <td valign="top" style="width: 34%;">
                    <div class="sidebar-card">
                        @if($hasEducation)
                            <div class="sidebar-title">Education</div>
                            @foreach($resume['education'] as $edu)
                                @if(is_array($edu))
                                    @php
                                        $institution = $safeText(data_get($edu, 'institution'));
                                        $studyType   = $safeText(data_get($edu, 'studyType'));
                                        $area        = $safeText(data_get($edu, 'area'));
                                        $range       = $fmtDateRange(data_get($edu, 'startDate'), data_get($edu, 'endDate'));
                                        $score       = $safeText(data_get($edu, 'score'));
                                    @endphp

                                    @if($institution !== '' || $studyType !== '' || $area !== '' || $range !== '' || $score !== '')
                                        <div class="sidebar-item avoid-break">
                                            @if($institution !== '')<strong>{{ $institution }}</strong>@endif
                                            @if(trim($studyType . ' ' . $area) !== '')
                                                <div class="meta-line accent">{{ trim($studyType . ' ' . $area) }}</div>
                                            @endif
                                            @if($range !== '')<div class="meta-line">{{ $range }}</div>@endif
                                            @if($score !== '')<div class="meta-line">Score: {{ $score }}</div>@endif
                                        </div>
                                    @endif
                                @endif
                            @endforeach

                            @if($hasSkills || $hasLanguages || $hasWebsites || $hasRefs)
                                <div class="divider"></div>
                            @endif
                        @endif

                        @if($hasSkills)
                            <div class="sidebar-title">Skills</div>
                            <div class="sidebar-item">
                                @if($skillsBody !== '')
                                    <div class="rich">{!! $skillsBody !!}</div>
                                @else
                                    @foreach($skillItems as $skill)
                                        <span class="pill">{{ $skill }}</span>
                                    @endforeach
                                @endif
                            </div>

                            @if($hasLanguages || $hasWebsites || $hasRefs)
                                <div class="divider"></div>
                            @endif
                        @endif

                        @if($hasLanguages)
                            <div class="sidebar-title">Languages</div>
                            @foreach($sidebarLanguages as $lang)
                                @php $l = $normalizeLanguage($lang); @endphp
                                @if($l['name'] !== '')
                                    <div class="sidebar-item tiny">
                                        <strong>{{ $l['name'] }}</strong>
                                        @if($l['meta'] !== '') <span class="muted">({{ $l['meta'] }})</span> @endif
                                    </div>
                                @endif
                            @endforeach

                            @if($hasWebsites || $hasRefs)
                                <div class="divider"></div>
                            @endif
                        @endif

                        @if($hasWebsites)
                            <div class="sidebar-title">Websites</div>
                            @foreach($websites as $site)
                                @php $w = $normalizeWebsite($site); @endphp
                                @if($w['url'] !== '')
                                    <div class="sidebar-item tiny">
                                        <strong>{{ $w['label'] }}:</strong>
                                        <div class="meta-line"><a href="{{ $w['url'] }}">{{ $w['url'] }}</a></div>
                                    </div>
                                @endif
                            @endforeach

                            @if($hasRefs)
                                <div class="divider"></div>
                            @endif
                        @endif

                        @if($hasRefs)
                            <div class="sidebar-title">References</div>
                            @foreach($resume['references'] as $r)
                                @if(is_array($r))
                                    @php
                                        $refName = $safeText(data_get($r, 'name'));
                                        $refBody = (string) data_get($r, 'reference', '');
                                    @endphp

                                    @if($refName !== '' || trim(strip_tags($refBody)) !== '')
                                        <div class="sidebar-item avoid-break">
                                            @if($refName !== '')<strong>{{ $refName }}</strong>@endif
                                            @if(trim(strip_tags($refBody)) !== '')
                                                <div class="rich" style="padding-top: 4px;">{!! $refBody !!}</div>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        @endif
                    </div>
                </td>
            @endif
        </tr>
    </table>
</div>
