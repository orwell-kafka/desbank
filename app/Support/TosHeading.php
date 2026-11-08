<?php

namespace App\Support;

/** Small formatting helpers for the generated ToS heading. */
class TosHeading
{
    /** 4 → "4", 1.5 → "1.5" (no trailing zeros). */
    public static function number($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 1, '.', ''), '0'), '.');
    }

    /** 4 → "4 units", 1 → "1 unit". Empty when the course has no credit units yet. */
    public static function units($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $n = self::number($value);

        return $n . ($n === '1' ? ' unit' : ' units');
    }

    /** 5 → "5hrs/week". Empty when not set. */
    public static function hours($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return self::number($value) . 'hrs/week';
    }

    /** 1 → "I", 4 → "IV" (used for "Test IV" in the ToS item numbers). */
    public static function roman(int $n): string
    {
        $map = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
        $out = '';
        foreach ($map as $sym => $val) {
            while ($n >= $val) {
                $out .= $sym;
                $n -= $val;
            }
        }

        return $out;
    }
}
