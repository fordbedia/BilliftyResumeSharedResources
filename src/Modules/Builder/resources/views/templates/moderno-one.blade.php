<div class="moderno--theme invoice-root scheme cat">
  <div class="page">

	<table class="dompdf-table">
		<tr class="dompdf-col">
			<td class="left">
				<div class="name">
					<span>{{ $first }}</span>
					@if($middle)<span>{{ $middle }}</span>@endif
					@if($last)<span>{{ $last }}</span>@endif
				</div>

				<div class="contact">
					@if($addressLine)
						<div class="row">{{ $addressLine }}</div>
					@endif

					@if(data_get($basics, 'email'))
						<div class="row">{{ data_get($basics, 'email') }}</div>
					@endif

					@if(data_get($basics, 'phone'))
						<div class="row">{{ data_get($basics, 'phone') }}</div>
					@endif

					@foreach($urls as $u)
						<div class="row">
							<span class="muted">{{ data_get($u, 'label', 'www') }}:</span>
							{{ data_get($u, 'url') }}
						</div>
					@endforeach
				</div>

				@if(data_get($basics, 'summary'))
					<div class="summary">{{ data_get($basics, 'summary') }}</div>
				@endif

				@if($skillLines->isNotEmpty())
					<div class="section">
						<div class="section-title">Skills</div>
						<ul class="skill-list">
							@foreach($skillLines as $skill)
								<li>{{ $skill }}</li>
							@endforeach
						</ul>
					</div>
				@endif
			</td>

			<td class="right">
				@if($work->isNotEmpty())
					<div class="section">
						<div class="section-title">Work history</div>

						@foreach($work as $job)
							@php
								$position = data_get($job, 'position');
								$company = data_get($job, 'name') ?: data_get($job, 'company');
								$locationText = data_get($job, 'location');
								$range = fmt_range(data_get($job, 'startDate'), data_get($job, 'endDate'));
								$highlights = collect(data_get($job, 'highlights', []))->filter()->values();
							@endphp

							<div class="job">
								<div class="job-title">{{ $position ?: $company }}</div>

								<div class="job-meta">
									@if($company)<span>{{ $company }}</span>@endif
									@if($range)<span class="sep">|</span><span>{{ $range }}</span>@endif
									@if($locationText)<span class="sep">|</span><span>{{ $locationText }}</span>@endif
								</div>

								@if($highlights->isNotEmpty())
									<ul class="bullets">
										@foreach($highlights as $h)
											<li>{{ $h }}</li>
										@endforeach
									</ul>
								@endif
							</div>
						@endforeach
					</div>
				@endif

				@if($education->isNotEmpty())
					<div class="section">
						<div class="section-title">Education</div>

						@foreach($education as $edu)
							@php
								$studyType = data_get($edu, 'studyType');
								$area = data_get($edu, 'area');
								$institution = data_get($edu, 'institution');
								$eduRange = fmt_range(data_get($edu, 'startDate'), data_get($edu, 'endDate'));
							@endphp

							<div class="edu">
								<div class="edu-degree">
									{{ trim(($studyType ? $studyType . ' in ' : '') . ($area ?? '')) ?: ($studyType ?: 'Education') }}
								</div>
								<div class="muted">
									{{ $institution }}
									@if($eduRange) | {{ $eduRange }} @endif
								</div>
							</div>
						@endforeach
					</div>
				@endif

				@if($references->isNotEmpty())
					<div class="section">
						<div class="section-title">References</div>

						@foreach($references as $ref)
							@php
								$refName = data_get($ref, 'name');
								$refText = data_get($ref, 'reference');
							@endphp

							<div class="ref">
								<div style="font-weight: 800;">{{ $refName }}</div>
								@if($refText)
									<div class="muted">{{ $refText }}</div>
								@endif
							</div>
						@endforeach
					</div>
				@endif
			</td>
		</tr>
	</table>

	<style>
        /*@page { margin: 24px; }*/

        /** { box-sizing: border-box; }*/

        /*html, body {*/
        /*    margin: 0;*/
        /*    padding: 0;*/
        /*    width: 100%;*/
        /*}*/

        /*body {*/
        /*    font-family: DejaVu Sans, Arial, Helvetica, sans-serif;*/
        /*    font-size: 11px;*/
        /*    color: #111;*/
        /*    line-height: 1.35;*/
        /*}*/

        /* ✅ Main two-column table */
        /*table.layout {*/
        /*    width: 100%;*/
        /*    border-collapse: collapse;*/
        /*    table-layout: fixed;*/
        /*}*/

        td.left {
            width: 32%;
            vertical-align: top;
            background: #f4f4f4;
            padding: 18px 16px;
        }

        td.right {
            width: 68%;
            vertical-align: top;
            background: #fff;
            padding: 18px 18px;
        }

        /* Avoid table-row "keep together" behavior */
        /*tr { page-break-inside: auto; }*/

        /* Name block */
        /*.name {*/
        /*    font-size: 30px;*/
        /*    font-weight: 800;*/
        /*    letter-spacing: -0.4px;*/
        /*    line-height: 1.05;*/
        /*    margin: 0 0 10px 0;*/
        /*}*/
        /*.name span { display: block; }*/

        /*!* Contact *!*/
        /*.contact { margin: 6px 0 14px 0; color: #222; }*/
        /*.contact .row { margin: 2px 0; word-break: break-word; }*/

        /*.muted { color: #555; }*/

        /*!* Sections *!*/
        /*.section { margin-top: 16px; }*/

        /*.section-title {*/
        /*    font-size: 12px;*/
        /*    font-weight: 800;*/
        /*    text-transform: uppercase;*/
        /*    letter-spacing: 0.6px;*/
        /*    margin: 0 0 8px 0;*/

        /*    !* Prevent lonely titles at bottom of page *!*/
        /*    page-break-after: avoid;*/
        /*}*/

        /*.summary { margin-top: 10px; color: #222; }*/

        /*.skill-list { margin: 0; padding: 0; list-style: none; }*/
        /*.skill-list li { margin: 3px 0; }*/

        /*!* Work / Education / References blocks *!*/
        /*.job, .edu, .ref {*/
        /*    margin: 0 0 12px 0;*/
        /*    page-break-inside: avoid; !* keep each block together *!*/
        /*}*/

        /*.job-title { font-weight: 800; font-size: 12px; margin: 0 0 1px 0; }*/
        /*.job-meta { margin: 0 0 6px 0; color: #444; }*/
        /*.job-meta .sep { padding: 0 6px; color: #777; }*/

        /*.bullets { margin: 0; padding-left: 16px; }*/
        /*.bullets li { margin: 4px 0; }*/

        /*.edu-degree { font-weight: 800; margin: 0 0 1px 0; }*/
    </style>
	</div>
</div>