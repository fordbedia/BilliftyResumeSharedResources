{{-- builder::resume --}}
@php
    $template = $resume['template']['path'] ?? 'templates.moderno-one';

@endphp

@php
    function fmt_date($value) {
        if (!$value) return null;
        $ts = strtotime($value);
        if (!$ts) return $value;
        return date('F Y', $ts);
    }

    function fmt_range($start, $end) {
        $s = fmt_date($start);
        $e = $end ? fmt_date($end) : 'Present';
        if (!$s && !$end) return null;
        if (!$s) return $e;
        return $s . ' - ' . $e;
    }

    $basics = data_get($resume, 'basics', []);
    $location = data_get($basics, 'location', []);
    $profiles = collect(data_get($basics, 'profiles', []));
    $work = collect(data_get($resume, 'work', []));
    $education = collect(data_get($resume, 'education', []));
    $references = collect(data_get($resume, 'references', []));
    $skills = collect(data_get($resume, 'skills', []));

    // Name split for stacked look
    $fullName = (string) data_get($basics, 'name', '');
    $parts = preg_split('/\s+/', trim($fullName)) ?: [];
    $first = $parts[0] ?? $fullName;
    $middle = $parts[1] ?? '';
    $last = $parts[2] ?? '';

    if (count($parts) === 2) { $first = $parts[0]; $middle = ''; $last = $parts[1]; }
    if (count($parts) >= 4) { $first = $parts[0]; $middle = $parts[1]; $last = implode(' ', array_slice($parts, 2)); }

    $city = data_get($location, 'city');
    $region = data_get($location, 'region');
    $postal = data_get($location, 'postalCode');
    $addressLine = trim(collect([$city, $region])->filter()->join(', ') . ($postal ? " {$postal}" : ''));

    $urls = collect()
        ->when(data_get($basics, 'url'), fn($c) => $c->push(['label' => 'WWW', 'url' => data_get($basics, 'url')]))
        ->merge(
            $profiles->map(function ($p) {
                $u = data_get($p, 'url');
                if (!$u) return null;
                return ['label' => strtolower((string) data_get($p, 'network', 'WWW')), 'url' => $u];
            })->filter()->values()
        )->values();

    // Normalize skills
    $skillLines = collect();
    if ($skills->isNotEmpty() && is_array($skills->first())) {
        foreach ($skills as $s) {
            $name = data_get($s, 'name');
            if ($name) $skillLines->push($name);
            else foreach ((array) data_get($s, 'keywords', []) as $kw) $skillLines->push($kw);
        }
    } else {
        foreach ($skills as $s) $skillLines->push($s);
    }
    $skillLines = $skillLines->filter()->unique()->values();
@endphp

@extends('builder::main')

@section('content')
	<div class="invoice-page">
		@include("builder::$template")
	</div>
@endsection

@section('globalcss')
	<style>
		body, .page, .invoice-root {
			font-family: "DejaVu Sans", sans-serif !important;
		}
		h1, h2, h3 {
			margin: 0;
		}

		.text-right {
			text-align: right;
		}

		.text-left {
			text-align: left;
		}

		/*@page {*/
		/*	!* A4 portrait with normal margins *!*/
		/*	size: A4 portrait;*/
		/*	margin: 15mm;*/
		/*}*/

		body {
			margin: 0;
			padding: 0;
			font-family: DejaVu Sans, sans-serif;
			font-size: 11px;
		}

		.invoice-page {
			/* Page width = 210mm - left/right margins (15 + 15) = 180mm */
			/* Keep it slightly smaller to avoid edge issues */
			width: 180mm;
			margin: 0 auto;
		}
		.row-cols {
		  width: 100%;
		}

		/* Shared column style */
		.col {
		  float: left;
		}

		/* Left column: 50% - gutter */
		.col-left {
		  width: 48%;
		  margin-right: 4%;
		}

		/* Right column */
		.col-right {
		  width: 48%;
		}
		.watermark {
			text-align: center;
			font-size: 18px;
		}
		/**------------------------- Dompdf Safe ------------------------*/
		/* Section Wrapper */
		.dompdf-section {
		  background: #f4f4f4;
		  padding: 16px 22px;
		  clear: both;
		  overflow: visible;     /* SAFE */
		  display: block;
		}

		/* Title */
		.dompdf-section .section-title {
		  font-size: 14px;
		  margin: 0 0 10px 0;
		}

		/* The Table Layout (SUPER SAFE) */
		.dompdf-table {
		  width: 100%;
		  border-collapse: collapse;
		}

		.dompdf-col {
		  vertical-align: top;
		  padding: 0;
		}

		/* Column widths */
		.left-col {
		  width: 40%;
		  padding-right: 12px;
		}

		.right-col {
		  width: 60%;
		  padding-left: 12px;
		}

		/* Each Box/Card */
		.dompdf-box {
		  background: #fff;
		  padding: 20px;
		  box-shadow: 0 0 12px rgba(0,0,0,0.15);
		  margin-top: 4px;
		  border-radius: 6px; /* optional */
		}
	</style>
@endsection
