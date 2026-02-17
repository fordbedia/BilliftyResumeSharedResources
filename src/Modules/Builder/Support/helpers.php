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

/**
 * Given an HSL string like: "hsl(217 91% 60%)" (spaces or commas OK),
 * returns either a light or dark text color that contrasts well.
 *
 * Defaults:
 *  - If background is "dark" -> return white-ish text
 *  - If background is "light" -> return dark-ish text
 */
function contrastTextFromHsl(
    string $hsl,
    string $lightText = '#FFFFFF',
    string $darkText  = '#0F172A',
    float $threshold  = 0.50 // relative luminance threshold; tweak 0.45-0.60 if you want
): string {
    $rgb = hslToRgbFromString($hsl);
    if ($rgb === null) {
        // Fallback if parsing fails
        return $darkText;
    }

    [$r, $g, $b] = $rgb; // 0..255
    $lum = relativeLuminance($r, $g, $b); // 0..1

    // If background is dark, use light text
    return ($lum < $threshold) ? $lightText : $darkText;
}

/**
 * Parses "hsl(217 91% 60%)" or "hsl(217, 91%, 60%)" etc.
 * Returns [R,G,B] (0..255) or null if invalid.
 */
function hslToRgbFromString(string $hsl): ?array {
    $s = trim(strtolower($hsl));

    // Extract the inside of hsl(...)
    if (!preg_match('/hsl\((.*?)\)/', $s, $m)) {
        return null;
    }

    // Allow both comma-separated and space-separated
    $inside = trim($m[1]);
    $inside = str_replace(',', ' ', $inside);
    $inside = preg_replace('/\s+/', ' ', $inside);

    // Match: H  S%  L%
    if (!preg_match('/^(-?\d+(\.\d+)?)\s+(\d+(\.\d+)?)%\s+(\d+(\.\d+)?)%$/', $inside, $p)) {
        return null;
    }

    $h = (float)$p[1];
    $sPct = (float)$p[3];
    $lPct = (float)$p[5];

    // Normalize
    $h = fmod($h, 360.0);
    if ($h < 0) $h += 360.0;

    $s = max(0.0, min(1.0, $sPct / 100.0));
    $l = max(0.0, min(1.0, $lPct / 100.0));

    return hslToRgb($h, $s, $l);
}

/**
 * Converts HSL to RGB.
 * H: 0..360, S: 0..1, L: 0..1
 * Returns [R,G,B] (0..255)
 */
function hslToRgb(float $h, float $s, float $l): array {
    $c = (1 - abs(2 * $l - 1)) * $s;
    $hh = $h / 60.0;
    $x = $c * (1 - abs(fmod($hh, 2) - 1));

    $r1 = 0; $g1 = 0; $b1 = 0;

    if ($hh >= 0 && $hh < 1) { $r1 = $c; $g1 = $x; $b1 = 0; }
    elseif ($hh >= 1 && $hh < 2) { $r1 = $x; $g1 = $c; $b1 = 0; }
    elseif ($hh >= 2 && $hh < 3) { $r1 = 0; $g1 = $c; $b1 = $x; }
    elseif ($hh >= 3 && $hh < 4) { $r1 = 0; $g1 = $x; $b1 = $c; }
    elseif ($hh >= 4 && $hh < 5) { $r1 = $x; $g1 = 0; $b1 = $c; }
    else { $r1 = $c; $g1 = 0; $b1 = $x; }

    $m = $l - $c / 2.0;

    $r = (int)round(($r1 + $m) * 255);
    $g = (int)round(($g1 + $m) * 255);
    $b = (int)round(($b1 + $m) * 255);

    return [
        max(0, min(255, $r)),
        max(0, min(255, $g)),
        max(0, min(255, $b)),
    ];
}

/**
 * WCAG relative luminance (sRGB) in 0..1
 */
function relativeLuminance(int $r, int $g, int $b): float {
    $rs = $r / 255.0;
    $gs = $g / 255.0;
    $bs = $b / 255.0;

    $rLin = ($rs <= 0.04045) ? ($rs / 12.92) : pow(($rs + 0.055) / 1.055, 2.4);
    $gLin = ($gs <= 0.04045) ? ($gs / 12.92) : pow(($gs + 0.055) / 1.055, 2.4);
    $bLin = ($bs <= 0.04045) ? ($bs / 12.92) : pow(($bs + 0.055) / 1.055, 2.4);

    return (0.2126 * $rLin) + (0.7152 * $gLin) + (0.0722 * $bLin);
}
