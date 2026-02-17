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

    $hasSidebar = $hasLanguages || $hasWebsites;

    $basics = $resume['basics'] ?? [];
@endphp

<style>
    /* Minimal + Dompdf-friendly */
    * { box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.35;
        color: #111;
    }

    .container { width: 100%; }
    .muted { color: #666; }
    .tiny { font-size: 10px; }
    .sp-4 { height: 4px; }
    .sp-8 { height: 8px; }
    .sp-12 { height: 12px; }
    .sp-16 { height: 16px; }

    h1 {
        font-size: 22px;
        line-height: 1.15;
        margin: 0;
        padding: 0;
        letter-spacing: 0.2px;
    }

    h2 {
        font-size: 12px;
        margin: 0;
        padding: 0 0 6px 0;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        border-bottom: 1px solid #E6E6E6;
    }

    .section { padding-top: 12px; }
    .row { padding-top: 6px; }

    .pill {
        display: inline-block;
        border: 1px solid #E6E6E6;
        padding: 3px 7px;
        border-radius: 999px;
        margin: 0 6px 6px 0;
        font-size: 10px;
        color: #222;
        background: #FAFAFA;
        vertical-align: top;
        max-width: 100%;
        word-wrap: break-word;
    }

    .divider { border-top: 1px solid #EEE; margin: 10px 0; }

    /* Content styling for rich text bodies */
    .rich p { margin: 0 0 6px 0; }
    .rich ul { margin: 6px 0 0 16px; padding: 0; }
    .rich li { margin: 0 0 4px 0; }

    /* Work highlights */
    ul.clean { margin: 6px 0 0 16px; padding: 0; }
    ul.clean li { margin: 0 0 4px 0; }

    /* Sidebar */
    .sidebar-card {
        border: 1px solid #EEE;
        padding: 10px;
        border-radius: 8px;
        background: #FCFCFC;
    }
    .sidebar-title {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin: 0 0 6px 0;
        padding: 0 0 6px 0;
        border-bottom: 1px solid #EEE;
    }
    .sidebar-item { padding-top: 6px; }
    a { color: #111; text-decoration: none; }
</style>

<div class="container">
    {{-- ================= HEADER ================= --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
        <tr>
            <td valign="top">
                <h1>{{ $basics['name'] ?? 'Your Name' }}</h1>

                <div class="row muted">
                    {{ $basics['label'] ?? '' }}
                    @if(!empty(data_get($basics, 'location.city'))) • {{ data_get($basics, 'location.city') }} @endif
                    @if(!empty(data_get($basics, 'location.region'))) , {{ data_get($basics, 'location.region') }} @endif
                </div>

                <div class="row">
                    @if(!empty($basics['email'])) <span class="pill">{{ $basics['email'] }}</span> @endif
                    @if(!empty($basics['url'])) <span class="pill">{{ $basics['url'] }}</span> @endif
                </div>

                @if(!empty($basics['profiles']))
                    <div class="row">
                        @foreach($basics['profiles'] as $profile)
                            @php
                                $network = $profile['network'] ?? 'Profile';
                                $value = $profile['url'] ?? ($profile['username'] ?? '');
                            @endphp
                            @if(trim((string)$value) !== '')
                                <span class="pill">{{ $network }}: {{ $value }}</span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </td>
        </tr>
    </table>

    @if(!empty($basics['summary']))
        <div class="sp-8"></div>
        <div class="rich">{!! $basics['summary'] !!}</div>
    @endif

    <div class="sp-12"></div>

    {{-- ================= LAYOUT: MAIN + OPTIONAL SIDEBAR ================= --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
        <tr>
            {{-- MAIN --}}
            <td valign="top" style="{{ $hasSidebar ? 'width: 70%; padding-right: 14px;' : 'width: 100%;' }}">

                {{-- ================= SKILLS ================= --}}
                @if(!empty($resume['skills']))
                    <div class="section">
                        <h2>Skills</h2>
                        <div class="row">
                            @foreach($resume['skills'] as $skill)
                                <span class="pill">
                                    {{ is_array($skill) ? ($skill['name'] ?? '') : $skill }}
                                    @if(is_array($skill) && !empty($skill['level']))
                                        <span class="muted"> ({{ $skill['level'] }})</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ================= EDUCATION ================= --}}
                @if(!empty($resume['education']))
                    <div class="section">
                        <h2>Education</h2>

                        @foreach($resume['education'] as $edu)
                            <div class="row">
                                <strong>{{ $edu['institution'] ?? '' }}</strong>
                                @if(!empty($edu['studyType']) || !empty($edu['area']))
                                    — {{ trim(($edu['studyType'] ?? '') . ' ' . ($edu['area'] ?? '')) }}
                                @endif

                                <div class="muted">
                                    {{ $edu['startDate'] ?? '' }}
                                    @if(!empty($edu['endDate'])) – {{ $edu['endDate'] }} @endif
                                </div>

                                @if(!empty($edu['score']))
                                    <div class="muted">Score: {{ $edu['score'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- ================= EXPERIENCE ================= --}}
                @if(!empty($resume['work']))
                    <div class="section">
                        <h2>Experience</h2>

                        @foreach($resume['work'] as $work)
                            <div class="row">
                                <strong>{{ $work['position'] ?? '' }}</strong>
                                @if(!empty($work['name'])) — {{ $work['name'] }} @endif

                                <div class="muted">
                                    {{ $work['startDate'] ?? '' }}
                                    @if(!empty($work['endDate'])) – {{ $work['endDate'] }} @else – Present @endif
                                </div>

                                @if(!empty($work['summary']))
                                    <div class="rich">{!! $work['summary'] !!}</div>
                                @endif

                                @if(!empty($work['highlights']))
                                    <ul class="clean">
                                        @foreach($work['highlights'] as $h)
                                            @if(trim((string)$h) !== '')
                                                <li>{{ $h }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- ================= NEW RICH TEXT SECTIONS ================= --}}
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

                {{-- ================= REFERENCES ================= --}}
                @if(!empty($resume['references']))
                    <div class="section">
                        <h2>References</h2>

                        @foreach($resume['references'] as $r)
                            <div class="row">
                                <strong>{{ $r['name'] ?? '' }}</strong>
                                @if(!empty($r['reference']))
                                    <div class="rich" style="padding-top: 6px;">
                                        {!! $r['reference'] !!}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

            </td>

            {{-- SIDEBAR --}}
            @if($hasSidebar)
                <td valign="top" style="width: 30%;">
                    <div class="sidebar-card">

                        @if($hasWebsites)
                            <div class="sidebar-title">Websites</div>

                            @foreach($websites as $site)
                                @php
                                    // Accept either string or array formats
                                    $label = is_array($site) ? (data_get($site, 'label') ?? data_get($site, 'name') ?? 'Website') : 'Website';
                                    $url   = is_array($site) ? (data_get($site, 'url') ?? data_get($site, 'value') ?? '') : (string) $site;
                                    $url   = trim((string) $url);
                                @endphp

                                @if($url !== '')
                                    <div class="sidebar-item tiny">
                                        <strong>{{ $label }}:</strong>
                                        <div class="muted" style="padding-top: 2px;">
                                            <a href="{{ $url }}">{{ $url }}</a>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            @if($hasLanguages)
                                <div class="divider"></div>
                            @else
                                <div class="sp-8"></div>
                            @endif
                        @endif

                        @if($hasLanguages)
							<div class="sidebar-title">Languages</div>
							<div class="row">
								@foreach($sidebarLanguages as $lang)
									@php
										$label = '';

										if (is_array($lang)) {
											$name = data_get($lang, 'language') ?? data_get($lang, 'name') ?? '';
											$fluency = data_get($lang, 'fluency') ?? '';
											$label = trim($name . ($fluency ? ' (' . $fluency . ')' : ''));
										} else {
											$label = trim((string) $lang);
										}
									@endphp

									@if($label !== '')
										<span class="pill">{{ $label }}</span>
									@endif
								@endforeach
							</div>
						@endif
                    </div>
                </td>
            @endif
        </tr>
    </table>
</div>
