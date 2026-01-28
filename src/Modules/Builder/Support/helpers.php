<?php

if (! function_exists('fmt_date')) {
    function fmt_date(?string $value): string
    {
        if (! $value) {
            return '';
        }

        return \Carbon\Carbon::parse($value)->format('M Y');
    }
}

if (! function_exists('fmt_range')) {
	function fmt_range($start, $end): ?string
	{
		$s = fmt_date($start);
		$e = $end ? fmt_date($end) : 'Present';
		if (!$s && !$end) return null;
		if (!$s) return $e;
		return $s . ' - ' . $e;
	}
}