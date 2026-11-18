<?php

namespace App\Support;

/** Helps tell when two questions in the bank are really the same item (same words, different type). */
class QuestionText
{
    /** Lower-case, punctuation and spacing removed, so "What is PHP?" and "what is  php" match. */
    public static function normalize(?string $text): string
    {
        $text = mb_strtolower((string) $text);
        $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text) ?? '';

        return trim($text);
    }
}
