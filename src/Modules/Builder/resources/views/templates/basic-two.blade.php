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
@endphp

<style>
    /* ===== Modern Minimal (PDF-safe: tables + inline-block) ===== */
    * { box-sizing: border-box; }

    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.45;
        color: #0F172A;
    }

    .muted { color: #64748B; }
    .tiny  { font-size: 10px; }
    .micro { font-size: 9px; }

    .wrap { width: 100%; }

    /* Header card */
    .header {
        border: 1px solid #E7ECF3;
        background: #FBFCFE;
        border-radius: 12px;
        padding: 14px;
    }

    .name {
        font-size: 22px;
        line-height: 1.1;
        margin: 0;
        padding: 0;
        letter-spacing: 0.2px;
    }

    .headline { padding-top: 4px; }

    .chip {
        display: inline-block;
        border: 1px solid #E6EAF0;
        background: #FFFFFF;
        padding: 4px 9px;
        border-radius: 999px;
        margin: 0 6px 6px 0;
        font-size: 10px;
        color: #0F172A;
        vertical-align: top;
        word-wrap: break-word;
    }

    .chip-soft {
        background: #F5F7FB;
        border-color: #E7ECF3;
        color: #334155;
    }

    .dot { color: #CBD5E1; padding: 0 6px; }

    .sp-8  { height: 8px; }
    .sp-12 { height: 12px; }

    /* Layout cards */
    .card {
        border: 1px solid #E7ECF3;
        background: #FFFFFF;
        border-radius: 12px;
        padding: 12px;
    }

    .card-subtle { background: #FBFCFE; }

    /* Section titles */
    .section { padding-top: 12px; }
    .section:first-child { padding-top: 0; }

    .title {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        margin: 0;
        padding: 0;
        color: #0F172A;
    }

    .rule { border-top: 1px solid #EEF2F7; margin-top: 8px; }

    .row { padding-top: 8px; }

    /* Items */
    .item { padding-top: 10px; }
    .item:first-child { padding-top: 8px; }

    .item-title { font-weight: bold; color: #0F172A; }
    .item-meta  { padding-top: 2px; color: #64748B; font-size: 10px; }

    /* Rich content */
    .rich p { margin: 0 0 6px 0; }
    .rich ul { margin: 6px 0 0 16px; padding: 0; }
    .rich li { margin: 0 0 4px 0; }

    ul.clean { margin: 6px 0 0 16px; padding: 0; }
    ul.clean li { margin: 0 0 4px 0; }

    a { color: #0F172A; text-decoration: none; }
</style>

<div class="wrap">

    {{-- ========================= HEADER ========================= --}}
    <div class="header">
        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
            <tr>
                <td valign="top">
                    <h1 class="name">{{ $basics['name'] ?? 'Your Name' }}</h1>

                    @if($label !== '' || $city !== '' || $region !== '')
                        <div class="headline muted">
                            @if($label !== '') {{ $label }} @endif
                            @if($city !== '')
                                <span class="dot">•</span>{{ $city }}
                            @endif
                            @if($region !== '')
                                @if($city !== '') , @endif {{ $region }}
                            @endif
                        </div>
                    @endif

                    <div class="sp-8"></div>

                    {{-- contact chips --}}
                    @if($email !== '') <span class="chip">{{ $email }}</span> @endif
                    @if($url !== '') <span class="chip">{{ $url }}</span> @endif

                    @if(!empty($basics['profiles']) && is_array($basics['profiles']))
                        @foreach($basics['profiles'] as $profile)
                            @php
                                $network = $safeText(data_get($profile, 'network')) ?: 'Profile';
                                $value   = $safeText(data_get($profile, 'url')) ?: $safeText(data_get($profile, 'username'));
                            @endphp
                            @if($value !== '')
                                <span class="chip chip-soft">{{ $network }}: {{ $value }}</span>
                            @endif
                        @endforeach
                    @endif

                    @if(!empty($basics['summary']))
                        <div class="sp-8"></div>
                        <div class="rich">{!! $basics['summary'] !!}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="sp-12"></div>

    {{-- ========================= MAIN + SIDEBAR ========================= --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
        <tr>
            {{-- MAIN --}}
            <td valign="top" style="{{ $hasSidebar ? 'width: 68%; padding-right: 12px;' : 'width: 100%;' }}">
                <div class="card">

                    {{-- Skills --}}
                    @if(!empty($resume['skills']) && is_array($resume['skills']))
                        <div class="section">
                            <div class="title">Skills</div>
                            <div class="rule"></div>

                            <div class="row">
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
                                    @endphp

                                    @if($skillName !== '')
                                        <span class="chip">
                                            {{ $skillName }}
                                            @if($skillLevel !== '')
                                                <span class="muted"> ({{ $skillLevel }})</span>
                                            @endif
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Education --}}
                    @if(!empty($resume['education']) && is_array($resume['education']))
                        <div class="section">
                            <div class="title">Education</div>
                            <div class="rule"></div>

                            @foreach($resume['education'] as $edu)
                                @if(is_array($edu))
                                    @php
                                        $institution = $safeText(data_get($edu, 'institution'));
                                        $studyType   = $safeText(data_get($edu, 'studyType'));
                                        $area        = $safeText(data_get($edu, 'area'));
                                        $score       = $safeText(data_get($edu, 'score'));
                                        $range       = $fmtDateRange(data_get($edu, 'startDate'), data_get($edu, 'endDate'));
                                        $sub         = trim($studyType . ' ' . $area);
                                    @endphp

                                    @if($institution !== '' || $sub !== '' || $range !== '' || $score !== '')
                                        <div class="item">
                                            <div class="item-title">
                                                {{ $institution }}
                                                @if($sub !== '')
                                                    <span class="muted"> — {{ $sub }}</span>
                                                @endif
                                            </div>
                                            @if($range !== '') <div class="item-meta">{{ $range }}</div> @endif
                                            @if($score !== '') <div class="item-meta">Score: {{ $score }}</div> @endif
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    @endif

                    {{-- Experience --}}
                    @if(!empty($resume['work']) && is_array($resume['work']))
                        <div class="section">
                            <div class="title">Experience</div>
                            <div class="rule"></div>

                            @foreach($resume['work'] as $work)
                                @if(is_array($work))
                                    @php
                                        $position = $safeText(data_get($work, 'position'));
                                        $company  = $safeText(data_get($work, 'name'));
                                        $range    = $fmtDateRange(data_get($work, 'startDate'), data_get($work, 'endDate'));
                                        $summary  = (string) data_get($work, 'summary', '');
                                        $highs    = (array) data_get($work, 'highlights', []);

                                        $hasHighs = false;
                                        foreach ($highs as $h) {
                                            if (is_string($h) && trim($h) !== '') { $hasHighs = true; break; }
                                        }
                                    @endphp

                                    @if($position !== '' || $company !== '' || $range !== '' || trim(strip_tags($summary)) !== '' || $hasHighs)
                                        <div class="item">
                                            <div class="item-title">
                                                {{ $position }}
                                                @if($company !== '')
                                                    <span class="muted"> — {{ $company }}</span>
                                                @endif
                                            </div>

                                            @if($range !== '') <div class="item-meta">{{ $range }}</div> @endif

                                            @if(trim(strip_tags($summary)) !== '')
                                                <div class="row rich">{!! $summary !!}</div>
                                            @endif

                                            @if($hasHighs)
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

                    {{-- New Rich Sections --}}
                    @if($hasProject)
                        <div class="section">
                            <div class="title">Projects</div>
                            <div class="rule"></div>
                            <div class="row rich">{!! $projectBody !!}</div>
                        </div>
                    @endif

                    @if($hasAccomplishment)
                        <div class="section">
                            <div class="title">Accomplishments</div>
                            <div class="rule"></div>
                            <div class="row rich">{!! $accomplishmentBody !!}</div>
                        </div>
                    @endif

                    @if($hasVolunteer)
                        <div class="section">
                            <div class="title">Volunteer</div>
                            <div class="rule"></div>
                            <div class="row rich">{!! $volunteerBody !!}</div>
                        </div>
                    @endif

                    @if($hasAffiliation)
                        <div class="section">
                            <div class="title">Affiliations</div>
                            <div class="rule"></div>
                            <div class="row rich">{!! $affiliationBody !!}</div>
                        </div>
                    @endif

                    @if($hasCertificate)
                        <div class="section">
                            <div class="title">Certifications</div>
                            <div class="rule"></div>
                            <div class="row rich">{!! $certificateBody !!}</div>
                        </div>
                    @endif

                    @if($hasInterest)
                        <div class="section">
                            <div class="title">Interests</div>
                            <div class="rule"></div>
                            <div class="row rich">{!! $interestBody !!}</div>
                        </div>
                    @endif

                    {{-- References --}}
                    @if(!empty($resume['references']) && is_array($resume['references']))
                        <div class="section">
                            <div class="title">References</div>
                            <div class="rule"></div>

                            @foreach($resume['references'] as $r)
                                @if(is_array($r))
                                    @php
                                        $refName = $safeText(data_get($r, 'name'));
                                        $refBody = (string) data_get($r, 'reference', '');
                                    @endphp

                                    @if($refName !== '' || trim(strip_tags($refBody)) !== '')
                                        <div class="item">
                                            @if($refName !== '')
                                                <div class="item-title">{{ $refName }}</div>
                                            @endif
                                            @if(trim(strip_tags($refBody)) !== '')
                                                <div class="row rich">{!! $refBody !!}</div>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    @endif

                </div>
            </td>

            {{-- SIDEBAR --}}
            @if($hasSidebar)
                <td valign="top" style="width: 32%;">
                    <div class="card card-subtle">

                        {{-- Websites --}}
                        @if($hasWebsites)
                            <div class="section">
                                <div class="title">Websites</div>
                                <div class="rule"></div>

                                @foreach($websites as $site)
                                    @php
                                        $w = $normalizeWebsite($site);
                                    @endphp

                                    @if($w['url'] !== '')
                                        <div class="row tiny" style="padding-top: 8px;">
                                            <div style="font-weight: bold;">{{ $w['label'] }}</div>
                                            <div class="muted" style="padding-top: 2px; word-wrap: break-word;">
                                                <a href="{{ $w['url'] }}">{{ $w['url'] }}</a>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        {{-- Languages --}}
                        @if($hasLanguages)
                            <div class="section">
                                <div class="title">Languages</div>
                                <div class="rule"></div>

                                <div class="row">
                                    @foreach($sidebarLanguages as $lang)
                                        @php
                                            $l = $normalizeLanguage($lang);
                                            $langLabel = $l['name'];
                                            if ($langLabel !== '' && $l['meta'] !== '') {
                                                $langLabel .= ' (' . $l['meta'] . ')';
                                            }
                                        @endphp

                                        @if($langLabel !== '')
                                            <span class="chip chip-soft">{{ $langLabel }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>
                </td>
            @endif
        </tr>
    </table>
</div>
