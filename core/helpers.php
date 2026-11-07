<?php

// Small view-layer helpers shared across all templates. Kept separate from
// the View class since these are plain functions, not static methods.

if (!function_exists('e')) {
    /** Shorthand for htmlspecialchars(..., ENT_QUOTES) — used on every dynamic output in views. */
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES);
    }
}

if (!function_exists('time_ago')) {
    function time_ago(string $datetime): string
    {
        $diff = time() - strtotime($datetime);
        if ($diff < 60) {
            return 'just now';
        }
        $units = [
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
        ];
        foreach ($units as $seconds => $label) {
            $count = intdiv($diff, $seconds);
            if ($count >= 1) {
                return $count . ' ' . $label . ($count > 1 ? 's' : '') . ' ago';
            }
        }
        return 'just now';
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $datetime, string $format = 'd M Y'): string
    {
        if (!$datetime) {
            return '—';
        }
        return date($format, strtotime($datetime));
    }
}
