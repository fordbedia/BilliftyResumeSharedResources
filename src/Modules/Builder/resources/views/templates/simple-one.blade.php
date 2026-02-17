@php
	$resume = $resume ?? [];
	$previewColorScheme = $previewColorScheme ?? null;
	$colorScheme = $previewColorScheme ?? data_get($resume, 'colorScheme');
@endphp

<style>
	/* -----------------------------
	   Page / Print setup (Playwright/Puppeteer friendly)
	----------------------------- */
	@page {
		size: A4;
		margin: 0.55in 1in 0.55in 1in;
	}

	html, body {
		margin: 0;
		padding: 0;
		background: #ffffff;
		color: #111827;
		font-family: Arial, Helvetica, sans-serif;
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
	}

	/* A4/Letter preview container (optional; harmless for print) */
	.page {
		width: 6.5in;
		min-height: 11in;
		margin: 0 auto;
		box-sizing: border-box;
	}

	/* -----------------------------
	   Typography + Colors (matching PDF vibe)
	----------------------------- */
	:root{
		--ink: {{$colorScheme}};   /* color scheme for headings */
		--text: #111827;
		--muted: #6b7280;
		--line: #111827;
	}

	.name {
		font-size: 65px;
		line-height: 0.95;
		font-weight: 800;
		letter-spacing: -1px;
		color: var(--ink);
		margin: 0;
	}

	.section-title {
		font-size: 28px;
		font-weight: 800;
		letter-spacing: 0.5px;
		color: var(--ink);
		margin: 0 0 12px 0;
	}

	/* NEW: label/title for injected sections (Accomplishments, Certifications, etc.) */
	.subsection-title {
		font-size: 22px;
		font-weight: 800;
		letter-spacing: 0.2px;
		color: var(--ink);
		margin: 0 0 10px 0;
	}

	.job-title {
		font-size: 22px;
		font-weight: 800;
		margin: 0 0 6px 0;
		color: #111827;
	}

	.job-meta {
		font-size: 15px;
		font-style: italic;
		color: #111827;
		margin: 0 0 10px 0;
	}

	.summary {
		font-size: 15px;
		line-height: 1.55;
		color: var(--muted);
		margin: 14px 0 14px 0;
	}

	.contact {
		text-align: left;
		font-size: 14px;
		line-height: 1.6;
		color: var(--muted);
	}

	.contact a {
		color: var(--muted);
		text-decoration: none;
	}

	.contact a:hover {
		text-decoration: underline;
	}

	.hr {
		height: 2px;
		background: var(--line);
		width: 100%;
		margin: 18px 0;
	}

	/* -----------------------------
	   Layout blocks
	----------------------------- */
	.top {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		gap: 24px;
	}

	.top-left { flex: 1 1 auto; }
	.top-right { width: 260px; padding-top: 10px; }

	.body {
		display: flex;
		align-items: flex-start;
		gap: 24px;
	}

	.left {
		flex: 1 1 auto;
		padding-right: 22px;
	}

	.right {
		width: 260px;
		border-left: 2px solid var(--line);
		padding-left: 22px;
		box-sizing: border-box;
	}

	/* Bullet list styling to mimic PDF spacing */
	.bullets {
		list-style: none;
		padding: 0;
		margin: 10px 0 18px 0;
	}

	.bullets li {
		position: relative;
		padding-left: 28px;
		margin: 0 0 10px 0;
		color: var(--muted);
		line-height: 1.55;
		font-size: 15px;
	}

	.bullets li:before {
		content: "•";
		position: absolute;
		left: 6px;
		top: -1px;
		font-size: 22px;
		color: #111827;
		line-height: 1;
	}

	/* Skills list (same bullet vibe, tighter) */
	.skills {
		list-style: none;
		padding: 0;
		margin: 8px 0 14px 0;
	}

	.skills li {
		position: relative;
		padding-left: 26px;
		margin: 0 0 10px 0;
		color: var(--muted);
		font-size: 15px;
		line-height: 1.45;
	}

	.skills li:before {
		content: "•";
		position: absolute;
		left: 6px;
		top: -1px;
		font-size: 22px;
		color: #111827;
		line-height: 1;
	}

	/* Education blocks */
	.edu-block { margin-top: 10px; }
	.edu-degree {
		font-size: 18px;
		font-weight: 800;
		color: #111827;
		margin: 0 0 6px 0;
		line-height: 1.25;
	}
	.edu-meta {
		font-size: 14px;
		color: var(--muted);
		margin: 0 0 18px 0;
		line-height: 1.55;
	}

	/* References */
	.ref-name {
		font-size: 16px;
		font-weight: bold;
		color: var(--muted);
		margin: 8px 0 2px 0;
	}
	.ref-lines {
		font-size: 14px;
		color: var(--muted);
		line-height: 1.6;
	}

	/* Prevent ugly splits in PDF */
	.avoid-break {
		break-inside: avoid;
		page-break-inside: avoid;
	}

	.avatar-wrap {
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

	.flex { display: flex; }
	.flex-column { flex-direction: column; }
	.gap-1 { gap: 1rem; }

	/* Optional: keep headings from orphaning */
	h2, h3 { break-after: avoid; page-break-after: avoid; }

	/* -----------------------------
	   Enhancements (keeps same concept)
	   - consistent spacing for injected rich sections
	   - avoid "double HRs" and awkward top HR
	   - make Languages/Websites match right-column typography
	----------------------------- */
	.rich-block { color: var(--muted); font-size: 15px; line-height: 1.55; }
	.rich-block p { margin: 10px 0; }
	.rich-block ul { margin: 10px 0 18px 0; padding-left: 18px; }
	.rich-block li { margin: 0 0 8px 0; }
	.rich-block hr { display: none; } /* prevents user HTML from adding extra rules */

	/* compact items for sidebar-like sections in right column */
	.side-list { margin: 6px 0 0 0; }
	.side-item {
		font-size: 15px;
		color: var(--muted);
		line-height: 1.45;
		margin: 0 0 10px 0;
		word-break: break-word;
	}
	.side-item a { color: var(--muted); text-decoration: none; border-bottom: 1px solid rgba(17,24,39,.25); }
</style>

@php
	$resume = $resume ?? [];
	$basic = data_get($resume, 'basics', []);
	$work = (array) data_get($resume, 'work', []);
	$skills = (array) data_get($resume, 'skills', []);
	$education = (array) data_get($resume, 'education', []);
	$references = (array) data_get($resume, 'references', []);

	$fmtDates = function ($start, $end) {
		$start = trim((string)($start ?? ''));
		$end = trim((string)($end ?? ''));
		if ($start && $end) return "{$start} - {$end}";
		return $start ?: $end;
	};

	// New sections (rich text bodies)
	$accomplishmentActive = (bool) data_get($resume, 'accomplishment.is_active');
	$affiliationActive    = (bool) data_get($resume, 'affiliation.is_active');
	$certificateActive    = (bool) data_get($resume, 'certificate.is_active');
	$interestActive       = (bool) data_get($resume, 'interest.is_active');
	$volunteerActive      = (bool) data_get($resume, 'volunteer.is_active');
	$projectActive        = (bool) data_get($resume, 'project.is_active');

	$accomplishmentBody = (string) data_get($resume, 'accomplishment.body', '');
	$affiliationBody    = (string) data_get($resume, 'affiliation.body', '');
	$certificate         = (string) data_get($resume, 'certificate.body', '');
	$interestBody       = (string) data_get($resume, 'interest.body', '');
	$volunteerBody      = (string) data_get($resume, 'volunteer.body', '');
	$projectBody        = (string) data_get($resume, 'project.body', '');

	// Sidebar extras
	$languagesActive = (bool) data_get($resume, 'languages.is_active');
	$languages = (array) data_get($resume, 'languages.languages', []);
	$websitesActive = (bool) data_get($resume, 'websites.is_active');
	$websites = (array) data_get($resume, 'websites.websites', []);

	$isFilled = fn($v) => isset($v) && trim((string)$v) !== '';

	// Each section now has a label/title
	$richSections = [
		['key' => 'accomplishments', 'label' => 'ACCOMPLISHMENTS', 'active' => $accomplishmentActive, 'body' => $accomplishmentBody],
		['key' => 'affiliations',    'label' => 'AFFILIATIONS',    'active' => $affiliationActive,    'body' => $affiliationBody],
		['key' => 'certifications',  'label' => 'CERTIFICATIONS',  'active' => $certificateActive,    'body' => $certificate],
		['key' => 'interests',       'label' => 'INTERESTS',       'active' => $interestActive,       'body' => $interestBody],
		['key' => 'volunteer',       'label' => 'VOLUNTEER',       'active' => $volunteerActive,      'body' => $volunteerBody],
		['key' => 'projects',        'label' => 'PROJECTS',        'active' => $projectActive,        'body' => $projectBody],
	];

	$richSections = array_values(array_filter($richSections, function ($s) use ($isFilled) {
		return !empty($s['active']) && $isFilled($s['body'] ?? '');
	}));

	$image = data_get($basic, 'imageUrl');
@endphp

<div class="page">

	{{-- Header --}}
	<div class="top">
		<div class="top-left">
			@php
				$name = (string)($basic['name'] ?? '');
				$nameParts = preg_split('/\s+/', trim($name));
				$firstLine = implode(' ', array_slice($nameParts, 0, max(1, count($nameParts)-1)));
				$lastWord  = count($nameParts) ? $nameParts[count($nameParts)-1] : '';
			@endphp

			{{-- Mimic 2-line big name like the PDF --}}
			<div class="{{ $image ? 'flex flex-column avatar-wrap gap-1' : '' }}">
				@if ($image)
					<img src="{{ $image }}" class="avatar" alt="Photo Image" />
				@endif

				<h1 class="name">
					{{ $firstLine ?: $name }}
					@if($lastWord && $firstLine !== $name)
						<br>{{ $lastWord }}
					@endif
				</h1>
			</div>
		</div>

		<div class="top-right contact">
			@if(!empty($basic['location']))
				<div>
					{!! data_get($basic, 'location.address', '') !!}
					{!! data_get($basic, 'location.city', '') !!}
					{!! data_get($basic, 'location.region', '') !!}
					{!! data_get($basic, 'location.postalCode', '') !!}
				</div>
			@endif

			@if(!empty($basic['email']))
				<div><a href="mailto:{{ $basic['email'] }}">{{ $basic['email'] }}</a></div>
			@endif

			@if(!empty($basic['phone']))
				<div>{{ $basic['phone'] }}</div>
			@endif

			@if(!empty($basic['website']))
				@php $w = trim((string)$basic['website']); @endphp
				<div>WWW: <a href="{{ str_starts_with($w, 'http') ? $w : 'https://' . $w }}">{{ $w }}</a></div>
			@endif

			@if(!empty($basic['linkedin']))
				@php $l = trim((string)$basic['linkedin']); @endphp
				<div>WWW: <a href="{{ str_starts_with($l, 'http') ? $l : 'https://' . $l }}">{{ $l }}</a></div>
			@endif
		</div>
	</div>

	<div class="hr"></div>

	{{-- Summary --}}
	@if(!empty($basic['summary']))
		<p class="summary">
			{!! $basic['summary'] !!}
		</p>
	@endif

	<div class="hr"></div>

	{{-- ===================== Labeled Rich Sections ===================== --}}
	@if(!empty($richSections))
		<div class="rich-block">
			@foreach ($richSections as $i => $sec)
				{{-- Divider only between blocks (prevents awkward top divider) --}}
				@if($i > 0)
					<div class="hr"></div>
				@endif

				<h3 class="subsection-title">{{ $sec['label'] }}</h3>
				{!! $sec['body'] !!}
			@endforeach
		</div>

		{{-- Keep concept: strong divider before 2-column body --}}
		<div class="hr"></div>
	@endif

	{{-- 2-column body --}}
	<div class="body">

		{{-- LEFT: Work History + References --}}
		<div class="left">

			<h2 class="section-title">WORK HISTORY</h2>

			@foreach($work as $job)
				<div class="avoid-break" style="margin-bottom: 22px;">
					<h3 class="job-title">{{ $job['position'] ?? '' }}</h3>

					@php
						$company = $job['company'] ?? '';
						$loc = $job['location'] ?? '';
						$dates = $fmtDates($job['startDate'] ?? '', $job['endDate'] ?? '');
						$metaParts = array_filter([$company, $loc ? $loc : null]);
						$metaLeft = implode(', ', $metaParts);
					@endphp

					<div class="job-meta">
						{{ $metaLeft }}
						@if($dates)
							{{ $metaLeft ? ' | ' : '' }}{{ $dates }}
						@endif
					</div>

					@if(!empty($job['summary']))
						{!! $job['summary'] !!}
					@endif

					@if(!empty($job['highlights']) && is_array($job['highlights']))
						<ul class="bullets">
							@foreach($job['highlights'] as $hl)
								@if($hl !== null && trim((string)$hl) !== '')
									<li>{{ $hl }}</li>
								@endif
							@endforeach
						</ul>
					@endif
				</div>
			@endforeach

			@if(!empty($references))
				<div class="avoid-break" style="margin-top: 8px;">
					<h2 class="section-title" style="margin-top: 8px;">REFERENCES</h2>

					@foreach($references as $ref)
						<div style="margin-top: 14px;">
							<div class="ref-name">{{ $ref['name'] ?? '' }}</div>
							<div class="ref-lines">
								@if(!empty($ref['reference']))
									<div>
										{!! $ref['reference'] !!}
									</div>
								@endif
							</div>
						</div>
					@endforeach
				</div>
			@endif

		</div>

		{{-- RIGHT: Skills + Education + Languages + Websites --}}
		<div class="right">

			<h2 class="section-title">SKILLS</h2>

			@if(!empty($skills))
				<ul class="skills">
					@foreach($skills as $skill)
						@php
							$skillText = is_array($skill) ? ($skill['name'] ?? '') : (string)$skill;
							$skillText = trim((string)$skillText);
						@endphp
						@if($skillText !== '')
							<li>{{ $skillText }}</li>
						@endif
					@endforeach
				</ul>
			@endif

			<div class="hr" style="margin: 18px 0;"></div>

			<h2 class="section-title">EDUCATION</h2>

			@foreach($education as $edu)
				<div class="edu-block avoid-break">
					@php
						$degreeLine1 = trim(($edu['studyType'] ?? ''));
						$degreeLine2 = trim(($edu['area'] ?? ''));
						$institution = trim(($edu['institution'] ?? ''));
						$loc = trim(($edu['location'] ?? ''));
						$end = trim(($edu['endDate'] ?? ''));
					@endphp

					<div class="edu-degree">
						{{ $degreeLine1 }}
						@if($degreeLine2)
							<br>{{ $degreeLine2 }}
						@endif
					</div>

					<div class="edu-meta">
						{{ $institution }}
						@if($loc)
							{{ $institution ? ', ' : '' }}{{ $loc }}
						@endif
						@if($end)
							{{ ($institution || $loc) ? ' | ' : '' }}{{ $end }}
						@endif
					</div>
				</div>
			@endforeach

			@if ($languagesActive && !empty($languages))
				<div class="hr" style="margin: 18px 0;"></div>
				<h2 class="section-title">LANGUAGES</h2>

				<div class="side-list">
					@foreach ($languages as $language)
						@php $lang = trim((string)($language['language'] ?? '')); @endphp
						@if($lang !== '')
							<div class="side-item">{{ $lang }}</div>
						@endif
					@endforeach
				</div>
			@endif

			@if ($websitesActive && !empty($websites))
				<div class="hr" style="margin: 18px 0;"></div>
				<h2 class="section-title">WEBSITES</h2>

				<div class="side-list">
					@foreach ($websites as $website)
						@php
							$url = trim((string)($website['url'] ?? ''));
							$href = $url ? (str_starts_with($url, 'http') ? $url : 'https://' . $url) : '';
						@endphp

						@if($url !== '')
							<div class="side-item">
								<a href="{{ $href }}">{{ $url }}</a>
							</div>
						@endif
					@endforeach
				</div>
			@endif

		</div>

	</div>
</div>
