<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Resume</title>

    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 22px; margin: 0 0 4px; }
        h2 { font-size: 14px; margin: 18px 0 8px; border-bottom: 1px solid #ddd; padding-bottom: 6px; }
        .muted { color: #666; }
        .row { margin: 0 0 8px; }
        .pill { display: inline-block; padding: 2px 8px; border: 1px solid #ddd; border-radius: 999px; margin-right: 6px; }
        ul { margin: 6px 0 0 18px; padding: 0; }
        li { margin: 0 0 4px; }
    </style>
</head>
<body>

    @php($basics = $resume['basics'] ?? [])

    <h1>{{ $basics['name'] ?? 'Your Name' }}</h1>
    <div class="row muted">
        {{ $basics['label'] ?? '' }}
        @if(!empty($basics['location']['city'])) • {{ $basics['location']['city'] }} @endif
        @if(!empty($basics['location']['region'])) , {{ $basics['location']['region'] }} @endif
    </div>

    <div class="row">
        @if(!empty($basics['email'])) <span class="pill">{{ $basics['email'] }}</span> @endif
        @if(!empty($basics['url'])) <span class="pill">{{ $basics['url'] }}</span> @endif
    </div>

    @if(!empty($basics['summary']))
        <div class="row">{!! $basics['summary'] !!}</div>
    @endif

    @if(!empty($basics['profiles']))
        <div class="row">
            @foreach($basics['profiles'] as $profile)
                <span class="pill">
                    {{ $profile['network'] ?? 'Profile' }}:
                    {{ $profile['url'] ?? ($profile['username'] ?? '') }}
                </span>
            @endforeach
        </div>
    @endif

	    {{-- ================= SKILLS ================= --}}
    @if(!empty($resume['skills']))
        <h2>Skills</h2>

        <div class="row" style="padding-top: 12px;">
            @foreach($resume['skills'] as $skill)
                <span class="pill">
                    {{ $skill['name'] ?? $skill }}
                    @if(!empty($skill['level']))
                        <span class="muted" style="margin-bottom: 12px;"> ({{ $skill['level'] }})</span>
                    @endif
                </span>
            @endforeach
        </div>
    @endif

    {{-- ================= EDUCATION ================= --}}
    @if(!empty($resume['education']))
        <h2>Education</h2>

        @foreach($resume['education'] as $edu)
            <div class="row">
                <strong>
                    {{ $edu['institution'] ?? '' }}
                </strong>
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
    @endif


    @if(!empty($resume['work']))
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
                    <div>{!! $work['summary'] !!}</div>
                @endif

                @if(!empty($work['highlights']))
                    <ul>
                        @foreach($work['highlights'] as $h)
                            <li>{{ $h }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    @endif

	@if(!empty($resume['references']))
		<h2>References</h2>

		@foreach($resume['references'] as $r)
			<div class="row" style="padding-top: 12px;">
				<strong>{{$r['name']}}</strong>
				<div style="padding-top: 12px;">
					{!! $r['reference'] !!}
				</div>
			</div>
		@endforeach
	@endif
</body>
</html>
