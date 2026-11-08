<?php

namespace App\Support;

/** A stable banner colour per course, so each course keeps the same look on the list and on its own page. */
class CourseTheme
{
    private const COLORS = ['#16324f', '#1f6f78', '#b9791f', '#2f6b4f', '#6b3f6e', '#52667a', '#9a3b34'];

    public static function color($course): string
    {
        return self::COLORS[abs(crc32((string) $course->courseId)) % count(self::COLORS)];
    }
}
