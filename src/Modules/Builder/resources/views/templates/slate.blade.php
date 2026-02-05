{{-- resources/views/resumes/slate.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>{{ $resume['basics']['name'] ?? 'Resume' }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <style>
    /* -----------------------------
      PDF-friendly base (Dompdf/Chromium)
    ------------------------------ */
    @page { size: A4; margin: 14mm; }
    * { box-sizing: border-box; }
    html, body { padding: 0; margin: 0; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, Helvetica, sans-serif;
      color: #111827;
      line-height: 1.35;
      font-size: 12px;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    /* Layout */
    .page {
      width: 100%;
      min-height: 100%;
    }

    /* Use table layout for maximum PDF stability */
    .layout {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }
    .layout td {
      vertical-align: top;
    }

    .left {
      padding-right: 12mm;
      width: 72%;
    }
    .right {
      width: 28%;
      padding-left: 12mm;
      border-left: 2px solid #111827;
    }

    /* Header */
    .name {
      font-size: 30px;
      font-weight: 800;
      letter-spacing: -0.02em;
      margin: 0;
    }
    .title {
      font-size: 14px;
      color: #6b7280;
      margin: 2px 0 10px 0;
      font-weight: 500;
    }

    .contact-row {
      margin-top: 6px;
      color: #6b7280;
      font-size: 11px;
    }
    .contact-item {
      display: inline-block;
      margin-right: 14px;
      margin-bottom: 4px;
      white-space: nowrap;
    }
    .contact-dot {
      display: inline-block;
      width: 6px;
      height: 6px;
      border-radius: 999px;
      background: #9ca3af;
      margin-right: 6px;
      transform: translateY(-1px);
    }

    /* Sections */
    .section {
      margin-top: 14px;
    }
    .section-title {
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 0.02em;
      text-transform: none;
      margin: 0 0 8px 0;
    }
    .muted { color: #6b7280; }

    /* About */
    .about {
      color: #374151;
      font-size: 12px;
    }

    /* Experience */
    .exp-block { margin-bottom: 12px; }
    .role-line {
      display: table;
      width: 100%;
      table-layout: fixed;
    }
    .role-left, .role-right {
      display: table-cell;
      vertical-align: top;
    }
    .role-left { padding-right: 8px; }
    .role-right {
      text-align: right;
      color: #6b7280;
      white-space: nowrap;
      width: 80px;
      font-size: 11px;
    }
    .role {
      font-weight: 800;
      font-size: 13px;
      margin: 0;
    }
    .company {
      margin: 1px 0 6px 0;
      color: #6b7280;
      font-size: 11px;
    }
    ul.bullets {
      margin: 0;
      padding-left: 16px;
      color: #374151;
    }
    ul.bullets li { margin: 0 0 4px 0; }

    /* Education */
    .edu-line {
      display: table;
      width: 100%;
      table-layout: fixed;
    }
    .edu-left, .edu-right {
      display: table-cell;
      vertical-align: top;
    }
    .edu-right {
      text-align: right;
      color: #6b7280;
      white-space: nowrap;
      width: 80px;
      font-size: 11px;
    }
    .degree { font-weight: 800; margin: 0; }
    .school { margin: 1px 0 0 0; color: #6b7280; font-size: 11px; }

    /* References */
    .ref-grid {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      margin-top: 6px;
    }
    .ref-grid td {
      width: 50%;
      padding-right: 10px;
      vertical-align: top;
    }
    .ref-name { font-weight: 800; margin: 0 0 2px 0; }
    .ref-meta { color: #6b7280; font-size: 11px; margin: 0; }
    .ref-contact { color: #6b7280; font-size: 11px; margin: 2px 0 0 0; }

    /* Right sidebar */
    .avatar-wrap {
      text-align: center;
      margin-top: 2mm;
      margin-bottom: 10mm;
    }
    .avatar {
      width: 86px;
      height: 86px;
      border-radius: 999px;
      border: 4px solid #ffffff;
      box-shadow: 0 6px 16px rgba(0,0,0,.10);
      object-fit: cover;
      display: inline-block;
      background: #e5e7eb;
    }

    .side-section {
      margin-bottom: 14px;
    }
    .side-title {
      font-weight: 800;
      font-size: 12px;
      margin: 0 0 8px 0;
    }

    /* Skills with bars */
    .skill-row {
      display: table;
      width: 100%;
      table-layout: fixed;
      margin-bottom: 8px;
    }
    .skill-name, .skill-level {
      display: table-cell;
      vertical-align: top;
    }
    .skill-name { font-size: 11px; color: #111827; }
    .skill-level {
      font-size: 10px;
      color: #6b7280;
      text-align: right;
      width: 72px;
      white-space: nowrap;
    }
    .bar {
      height: 4px;
      background: #e5e7eb;
      border-radius: 999px;
      margin-top: 4px;
      overflow: hidden;
    }
    .bar > span {
      display: block;
      height: 100%;
      background: #111827;
      border-radius: 999px;
    }

    /* Tool chips */
    .chips { margin-top: 4px; }
    .chip {
      display: inline-block;
      padding: 4px 8px;
      margin: 0 6px 6px 0;
      border: 1px solid #e5e7eb;
      border-radius: 999px;
      font-size: 10px;
      color: #374151;
      background: #ffffff;
      white-space: nowrap;
    }

    /* Languages */
    .lang-row {
      display: table;
      width: 100%;
      table-layout: fixed;
      margin-bottom: 6px;
    }
    .lang-name, .lang-level {
      display: table-cell;
    }
    .lang-name { font-size: 11px; color: #374151; }
    .lang-level {
      font-size: 10px;
      color: #6b7280;
      text-align: right;
      width: 72px;
      white-space: nowrap;
    }

    /* Interests */
    .interest {
      display: inline-block;
      margin: 0 10px 8px 0;
      font-size: 10px;
      color: #374151;
      white-space: nowrap;
    }
    .interest-ico {
      display: inline-block;
      width: 14px;
      height: 14px;
      border-radius: 4px;
      background: #111827;
      margin-right: 6px;
      transform: translateY(2px);
    }

    /* Small helpers */
    .spacer { height: 4px; }
    .hr-soft {
      height: 1px;
      background: #e5e7eb;
      margin: 10px 0;
    }
  </style>
</head>

<body>
  @php
    // Expecting a JSON-Resume-ish structure in $resume (array)
    $basics = $resume['basics'] ?? [];
    $work = $resume['work'] ?? [];
    $education = $resume['education'] ?? [];
    $references = $resume['references'] ?? [];
    $skills = $resume['skills'] ?? [];         // [{ name, level, keywords? }]
    $languages = $resume['languages'] ?? [];   // [{ language, fluency }]
    $interests = $resume['interests'] ?? [];   // [{ name, keywords? }]

    // Optional extra arrays you might store in DB
    $tools = $resume['tools'] ?? ($resume['meta']['tools'] ?? []); // simple list
    $photo = $basics['imageUrl'] ?? null;

    // Map typical skill levels to a percent
    $levelToPct = function ($level) {
      $l = strtolower(trim((string) $level));
      return match (true) {
        str_contains($l, 'expert') => 92,
        str_contains($l, 'advanced') => 78,
        str_contains($l, 'intermediate') => 58,
        str_contains($l, 'beginner') => 35,
        is_numeric($level) => max(0, min(100, (int) $level)),
        default => 65,
      };
    };

    $formatPhone = function ($phone) {
      return $phone; // keep as-is (you can format later)
    };
  @endphp

  <div class="page">
    <table class="layout">
      <tr>
        {{-- LEFT MAIN --}}
        <td class="left">
          {{-- Header --}}
          <h1 class="name">{{ $basics['name'] ?? 'Your Name' }}</h1>
          <div class="title">{{ $basics['label'] ?? ($resume['headline'] ?? 'Professional Title') }}</div>

          {{-- Contact row --}}
          <div class="contact-row">
            @php
              $email = $basics['email'] ?? null;
              $phone = $basics['phone'] ?? null;
              $location = $basics['location']['city'] ?? null;
              $region = $basics['location']['region'] ?? null;
              $locText = trim(($location ? $location : '') . ($region ? ', ' . $region : ''));
              $profiles = $basics['profiles'] ?? [];
              $linkedIn = null;

              foreach ($profiles as $p) {
                $network = strtolower($p['network'] ?? '');
                if ($network === 'linkedin') { $linkedIn = $p['url'] ?? ($p['username'] ?? null); break; }
              }
            @endphp

            @if($email)
              <span class="contact-item"><span class="contact-dot"></span>{{ $email }}</span>
            @endif

            @if($phone)
              <span class="contact-item"><span class="contact-dot"></span>{{ $formatPhone($phone) }}</span>
            @endif

            @if($locText)
              <span class="contact-item"><span class="contact-dot"></span>{{ $locText }}</span>
            @endif

            @if($linkedIn)
              <span class="contact-item"><span class="contact-dot"></span>{{ $linkedIn }}</span>
            @endif
          </div>

          {{-- About --}}
          @if(!empty($basics['summary']))
            <div class="section">
              <div class="section-title">About</div>
              <div class="about">{!! $basics['summary'] !!}</div>
            </div>
          @endif

          {{-- Experience --}}
          @if(!empty($work))
            <div class="section">
              <div class="section-title">Experience</div>

              @foreach($work as $job)
                @php
                  $position = $job['position'] ?? 'Role';
                  $company = $job['name'] ?? ($job['company'] ?? '');
                  $start = $job['startDate'] ?? null;
                  $end = $job['endDate'] ?? null;
                  $endLabel = $end ?: 'Present';
                  $dateLabel = ($start ? substr($start, 0, 4) : '') . ($start ? ' - ' : '') . ($endLabel ? (is_string($endLabel) ? (substr($endLabel,0,4) ?: $endLabel) : $endLabel) : '');
                  $highlights = $job['highlights'] ?? [];
				  $summary = $job['summary'] ?? null;
                @endphp

                <div class="exp-block">
                  <div class="role-line">
                    <div class="role-left">
                      <p class="role">{{ $position }}</p>
                      @if($company)
                        <div class="company">{{ $company }}</div>
                      @endif

						{!! $summary !!}

                      @if(!empty($highlights))
                        <ul class="bullets">
                          @foreach($highlights as $h)
                            <li>{{ $h }}</li>
                          @endforeach
                        </ul>
                      @endif
                    </div>
                    <div class="role-right">
                      {{ trim($dateLabel) }}
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @endif

          {{-- Education --}}
          @if(!empty($education))
            <div class="section">
              <div class="section-title">Education</div>

              @foreach($education as $edu)
                @php
                  $study = $edu['studyType'] ?? '';
                  $area = $edu['area'] ?? '';
                  $institution = $edu['institution'] ?? '';
                  $start = $edu['startDate'] ?? null;
                  $end = $edu['endDate'] ?? null;
                  $dateLabel = ($start ? substr($start, 0, 4) : '') . ($start ? ' - ' : '') . ($end ? substr($end, 0, 4) : '');
                  $degreeLine = trim(($study ? $study : '') . ($area ? ' in ' . $area : ''));
                @endphp

                <div style="margin-bottom: 10px;">
                  <div class="edu-line">
                    <div class="edu-left">
                      <p class="degree">{{ $degreeLine ?: ($area ?: 'Education') }}</p>
                      @if($institution)
                        <p class="school">{{ $institution }}</p>
                      @endif
                    </div>
                    <div class="edu-right">{{ $dateLabel }}</div>
                  </div>
                </div>
              @endforeach
            </div>
          @endif

          {{-- References --}}
          @if(!empty($references))
            <div class="section">
              <div class="section-title">References</div>

              <table class="ref-grid">
                <tr>
                  @foreach($references as $idx => $ref)
                    @php
                      $rname = $ref['name'] ?? 'Reference';
                      $rpos  = $ref['position'] ?? '';
                      $rorg  = $ref['company'] ?? '';
                      $remail = $ref['email'] ?? '';
                      $rphone = $ref['phone'] ?? '';
                      $meta = trim($rpos . ($rorg ? ', ' . $rorg : ''));
                    @endphp

                    <td>
                      <p class="ref-name">{{ $rname }}</p>
                      @if ($ref['reference'])
						  {!! $ref['reference'] !!}
						@endif
                    </td>

                    @if(($idx % 2) === 1)
                      </tr><tr>
                    @endif
                  @endforeach
                </tr>
              </table>
            </div>
          @endif
        </td>

        {{-- RIGHT SIDEBAR --}}
        <td class="right">
          {{-- Avatar --}}
            @if($photo)
				<div class="avatar-wrap">
              		<img class="avatar" src="{{ $photo }}" alt="Avatar" />
				</div>
            @endif

          {{-- Skills --}}
          @if(!empty($skills))
            <div class="side-section">
              <div class="side-title">Skills</div>

              @foreach($skills as $s)
                @php
                  $sname = $s['name'] ?? 'Skill';
                  $slevel = $s['level'] ?? '';
                  $pct = $levelToPct($slevel);
                @endphp

                <div style="margin-bottom: 10px;">
                  <div class="skill-row">
                    <div class="skill-name">{{ $sname }}</div>
                    <div class="skill-level">{{ $slevel }}</div>
                  </div>
                  <div class="bar"><span style="width: {{ $pct }}%;"></span></div>
                </div>
              @endforeach
            </div>
          @endif

          {{-- Tools --}}
          @if(!empty($tools))
            <div class="side-section">
              <div class="side-title">Tools</div>
              <div class="chips">
                @foreach($tools as $t)
                  <span class="chip">{{ is_array($t) ? ($t['name'] ?? 'Tool') : $t }}</span>
                @endforeach
              </div>
            </div>
          @endif

          {{-- Languages --}}
          @if(!empty($languages))
            <div class="side-section">
              <div class="side-title">Languages</div>

              @foreach($languages as $lang)
                @php
                  $lname = $lang['language'] ?? ($lang['name'] ?? 'Language');
                  $lflu = $lang['fluency'] ?? ($lang['level'] ?? '');
                @endphp
                <div class="lang-row">
                  <div class="lang-name">{{ $lname }}</div>
                  <div class="lang-level">{{ $lflu }}</div>
                </div>
              @endforeach
            </div>
          @endif

          {{-- Interests --}}
          @if(!empty($interests))
            <div class="side-section">
              <div class="side-title">Interests</div>

              <div>
                @foreach($interests as $i)
                  @php $iname = $i['name'] ?? (is_string($i) ? $i : 'Interest'); @endphp
                  <span class="interest"><span class="interest-ico"></span>{{ $iname }}</span>
                @endforeach
              </div>
            </div>
          @endif
        </td>
      </tr>
    </table>
  </div>
</body>
</html>
