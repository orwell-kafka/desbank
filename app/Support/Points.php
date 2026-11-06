<?php

namespace App\Support;

/**
 * One place that decides how points are shown: no trailing zeroes, always followed by the word
 * "Points" (20 -> "20 Points", 20.0 / "20.00" -> "20 Points", 2.5 -> "2.5 Points", 1 -> "1 Point").
 * Keep in step with fmtPoints() in public/js/tos-form.js.
 */
class Points
{
    public static function number($value): string
    {
        $text = rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');

        return ($text === '' || $text === '-0') ? '0' : $text;
    }

    public static function format($value): string
    {
        $number = (float) $value;

        return self::number($number) . (abs($number - 1) < 0.00001 ? ' Point' : ' Points');
    }
}
