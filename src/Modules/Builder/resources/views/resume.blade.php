{{-- builder::resume --}}
@php
    $template = $resume['template']['path'] ?? 'templates.moderno-one';

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
