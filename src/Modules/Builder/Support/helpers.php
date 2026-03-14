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
    $rgb = colorStringToRgb($hsl);
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
 * Supports: hsl()/hsla(), rgb()/rgba(), and #RGB/#RRGGBB.
 * Returns [R,G,B] (0..255) or null.
 */
function colorStringToRgb(string $value): ?array {
    $color = trim(strtolower($value));
    if ($color === '') {
        return null;
    }

    if (str_starts_with($color, '#')) {
        return hexToRgb($color);
    }

    if (str_starts_with($color, 'rgb(') || str_starts_with($color, 'rgba(')) {
        return rgbToRgbFromString($color);
    }

    if (str_starts_with($color, 'hsl(') || str_starts_with($color, 'hsla(')) {
        return hslToRgbFromString($color);
    }

    return null;
}

/**
 * Parses #RGB or #RRGGBB.
 */
function hexToRgb(string $hex): ?array {
    $raw = ltrim(trim($hex), '#');
    if ($raw === '') {
        return null;
    }

    if (strlen($raw) === 3) {
        $raw = $raw[0] . $raw[0] . $raw[1] . $raw[1] . $raw[2] . $raw[2];
    }

    if (strlen($raw) !== 6 || !ctype_xdigit($raw)) {
        return null;
    }

    return [
        hexdec(substr($raw, 0, 2)),
        hexdec(substr($raw, 2, 2)),
        hexdec(substr($raw, 4, 2)),
    ];
}

/**
 * Parses rgb(255, 255, 255) and rgba(255, 255, 255, 0.5).
 */
function rgbToRgbFromString(string $rgb): ?array {
    $s = trim(strtolower($rgb));
    if (!preg_match('/rgba?\((.*?)\)/', $s, $m)) {
        return null;
    }

    $inside = trim($m[1]);
    $inside = preg_replace('/\s+/', '', $inside);
    $parts = explode(',', $inside);
    if (count($parts) < 3) {
        return null;
    }

    $toChannel = function (string $part): ?int {
        if ($part === '') {
            return null;
        }
        if (str_ends_with($part, '%')) {
            $pct = (float) rtrim($part, '%');
            return (int) round(max(0, min(100, $pct)) * 2.55);
        }
        if (!is_numeric($part)) {
            return null;
        }
        return (int) round(max(0, min(255, (float) $part)));
    };

    $r = $toChannel($parts[0]);
    $g = $toChannel($parts[1]);
    $b = $toChannel($parts[2]);

    if ($r === null || $g === null || $b === null) {
        return null;
    }

    return [$r, $g, $b];
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
