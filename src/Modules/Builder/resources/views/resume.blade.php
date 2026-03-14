{{-- builder::resume --}}
@php
    $template = $templatePath ?? data_get($resume, 'template.path') ?? 'templates.moderno-one';

	$sectionOrderDefaults = [
		'basics',
		'work',
		'education',
		'skills',
		'references',
		'additional_information',
		'for_us_candidates',
	];

	$incomingSectionOrder = (array) data_get($resume, 'sectionOrder', data_get($resume, 'section_order', []));
	$sectionOrderAliases = [
		'basic' => 'basics',
		'basic_info' => 'basics',
		'personal_info' => 'basics',
		'experience' => 'work',
		'certificate' => 'additional_information',
		'certificates' => 'additional_information',
		'accomplishment' => 'additional_information',
		'accomplishments' => 'additional_information',
		'project' => 'for_us_candidates',
		'projects' => 'for_us_candidates',
		'affiliation' => 'for_us_candidates',
		'affiliations' => 'for_us_candidates',
		'interest' => 'for_us_candidates',
		'interests' => 'for_us_candidates',
		'website' => 'for_us_candidates',
		'websites' => 'for_us_candidates',
	];
	$sectionOrder = [];
	foreach ($incomingSectionOrder as $sectionKey) {
		if (!is_string($sectionKey)) {
			continue;
		}
		$normalizedKey = strtolower(trim($sectionKey));
		$normalizedKey = str_replace(['-', ' '], '_', $normalizedKey);
		$normalizedKey = $sectionOrderAliases[$normalizedKey] ?? $normalizedKey;

		if (!in_array($normalizedKey, $sectionOrderDefaults, true) || in_array($normalizedKey, $sectionOrder, true)) {
			continue;
		}
		$sectionOrder[] = $normalizedKey;
	}
	foreach ($sectionOrderDefaults as $defaultKey) {
		if (!in_array($defaultKey, $sectionOrder, true)) {
			$sectionOrder[] = $defaultKey;
		}
	}
	$sectionOrderPriority = array_flip($sectionOrder);
	$sectionOrderFor = function (string $key) use ($sectionOrderPriority): int {
		return ((int) ($sectionOrderPriority[$key] ?? 999)) + 1;
	};
	$sectionOrderStyle = function (string $key) use ($sectionOrderFor): string {
		return 'order: ' . $sectionOrderFor($key) . ';';
	};

	$sectionLabels = [
		'basics' => 'Basics',
		'work' => 'Experience',
		'education' => 'Education',
		'skills' => 'Skills',
		'references' => 'References',
		'additional_information' => 'Additional Information',
		'for_us_candidates' => 'For US Candidates',
	];

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

	$languagesActive   = (bool) data_get($resume, 'languages.is_active');
	$sidebarLanguages  = (array) data_get($resume, 'languages.languages', []);

	$websitesActive    = (bool) data_get($resume, 'websites.is_active');
	$websites          = (array) data_get($resume, 'websites.websites', []);

	$hasLanguages = $languagesActive && !empty($sidebarLanguages);
	$hasWebsites  = $websitesActive && !empty($websites);
	$hasAdditionalInformation = $hasCertificate || $hasAccomplishment || $hasLanguages;
	$hasForUsCandidates = $hasAffiliation || $hasInterest || $hasVolunteer || $hasWebsites || $hasProject;

@endphp

@extends('builder::main')

@section('content')
	<div class="invoice-page">
		@include("builder::$template")
	</div>
@endsection

@section('globalcss')
	<style>
		:root{
		  --foreground: 0 0% 10%;
		  --background: 0 0% 100%;
		  --muted-foreground: 0 0% 35%;
		  --primary: 222 89% 55%;
		}

		body{
		  color: #111;                  /* hard fallback */
		  background: #fff;
		  font-family: DejaVu Sans, sans-serif;
		}

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
		ul {
			margin: 6px 0 0 18px;
			padding: 0;
		}

		li {
			margin-bottom: 4px;
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
			font-size: 14px;
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
		.left{
			float: left;
		}
		.right{
			float: right;
		}
	</style>
@endsection
