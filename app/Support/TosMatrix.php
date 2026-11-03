<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * The hidden "topic x Bloom/type" distribution behind a ToS.
 *
 * Table 1 (topics) and Table 2 (Bloom level / question type) of a ToS are independent, so on their
 * own they cannot say how many items of each kind belong to each topic. This class fills that gap:
 * it splits every Table 2 row's items across the topics so that each topic's points land as close
 * as possible to its contact-hours share, while the grand total stays equal to the ToS total score.
 *
 * A matrix is a plain array: [topicId][bloomKey] => whole item count, where bloomKey is
 * "Bloom level|Question type" (see key()).
 */
class TosMatrix
{
    public static function key(string $bloomLevel, string $questionType): string
    {
        return $bloomLevel . '|' . $questionType;
    }

    /**
     * Split the items of each Table 2 row across the topics.
     *
     * @param  array<int|string, float>  $hoursByTopic  contact hours keyed by topic id
     * @param  array<string, array{points: float, count: int}>  $rows  Table 2 rows keyed by bloomKey
     * @param  array<int|string, array<string, int>>  $caps  optional upper limits [topicId][bloomKey]
     *         (for example how many questions the bank holds); a missing entry means "no limit"
     * @return array<int|string, array<string, int>>
     */
    public static function allocate(array $hoursByTopic, array $rows, array $caps = []): array
    {
        $topicIds = array_keys($hoursByTopic);
        $matrix = [];
        foreach ($topicIds as $topicId) {
            $matrix[$topicId] = array_fill_keys(array_keys($rows), 0);
        }

        $totalHours = array_sum($hoursByTopic);
        if (empty($topicIds) || empty($rows) || $totalHours <= 0) {
            return $matrix;
        }

        $totalScore = 0.0;
        foreach ($rows as $row) {
            $totalScore += $row['points'] * $row['count'];
        }

        $target = [];
        $current = [];
        foreach ($hoursByTopic as $topicId => $hours) {
            $target[$topicId] = ($hours / $totalHours) * $totalScore;
            $current[$topicId] = 0.0;
        }

        $hasRoom = function ($topicId, string $key) use (&$matrix, $caps): bool {
            if (! isset($caps[$topicId][$key])) {
                return true;
            }

            return $matrix[$topicId][$key] < $caps[$topicId][$key];
        };

        // Step 1: place the biggest items first; each goes to the topic furthest below its target.
        $keys = array_keys($rows);
        usort($keys, fn ($a, $b) => $rows[$b]['points'] <=> $rows[$a]['points']);

        foreach ($keys as $key) {
            for ($i = 0; $i < $rows[$key]['count']; $i++) {
                $candidates = array_values(array_filter($topicIds, fn ($t) => $hasRoom($t, $key)));
                if (empty($candidates)) {
                    $candidates = $topicIds; // bank too small for this row: ignore the limit, generate() reports it
                }

                $best = $candidates[0];
                foreach ($candidates as $t) {
                    if (($target[$t] - $current[$t]) > ($target[$best] - $current[$best]) + 1e-9) {
                        $best = $t;
                    }
                }

                $matrix[$best][$key]++;
                $current[$best] += $rows[$key]['points'];
            }
        }

        // Step 2: improve by moving single items, or swapping two different items, between topics
        // whenever that reduces the total distance from the targets.
        for ($pass = 0; $pass < 200; $pass++) {
            $improved = false;

            foreach ($keys as $key) {
                $p = $rows[$key]['points'];
                foreach ($topicIds as $a) {
                    if ($matrix[$a][$key] <= 0) {
                        continue;
                    }
                    foreach ($topicIds as $b) {
                        if ($a === $b || ! $hasRoom($b, $key) || $matrix[$a][$key] <= 0) {
                            continue;
                        }
                        $before = abs($current[$a] - $target[$a]) + abs($current[$b] - $target[$b]);
                        $after = abs($current[$a] - $p - $target[$a]) + abs($current[$b] + $p - $target[$b]);
                        if ($after < $before - 1e-9) {
                            $matrix[$a][$key]--;
                            $matrix[$b][$key]++;
                            $current[$a] -= $p;
                            $current[$b] += $p;
                            $improved = true;
                        }
                    }
                }
            }

            foreach ($keys as $k1) {
                foreach ($keys as $k2) {
                    $p1 = $rows[$k1]['points'];
                    $p2 = $rows[$k2]['points'];
                    if ($k1 === $k2 || abs($p1 - $p2) < 1e-9) {
                        continue;
                    }
                    // topic a gives one $k1 item to b and receives one $k2 item from b
                    foreach ($topicIds as $a) {
                        foreach ($topicIds as $b) {
                            if ($a === $b || $matrix[$a][$k1] <= 0 || $matrix[$b][$k2] <= 0
                                || ! $hasRoom($b, $k1) || ! $hasRoom($a, $k2)) {
                                continue;
                            }
                            $da = $p2 - $p1; // change for a
                            $before = abs($current[$a] - $target[$a]) + abs($current[$b] - $target[$b]);
                            $after = abs($current[$a] + $da - $target[$a]) + abs($current[$b] - $da - $target[$b]);
                            if ($after < $before - 1e-9) {
                                $matrix[$a][$k1]--;
                                $matrix[$b][$k1]++;
                                $matrix[$b][$k2]--;
                                $matrix[$a][$k2]++;
                                $current[$a] += $da;
                                $current[$b] -= $da;
                                $improved = true;
                            }
                        }
                    }
                }
            }

            if (! $improved) {
                break;
            }
        }

        return $matrix;
    }

    /**
     * Points each topic ends up with under a matrix.
     *
     * @param  array<int|string, array<string, int>>  $matrix
     * @param  array<string, float>  $pointsByKey  points per item keyed by bloomKey
     * @return array<int|string, float>
     */
    public static function topicPoints(array $matrix, array $pointsByKey): array
    {
        $out = [];
        foreach ($matrix as $topicId => $cells) {
            $sum = 0.0;
            foreach ($cells as $key => $count) {
                $sum += $count * ($pointsByKey[$key] ?? 0);
            }
            $out[$topicId] = round($sum, 2);
        }

        return $out;
    }

    /** True when every Table 2 row's items are fully (and only) distributed by the matrix. */
    public static function matchesRows(array $matrix, array $counts): bool
    {
        foreach ($counts as $key => $count) {
            $sum = 0;
            foreach ($matrix as $cells) {
                $sum += (int) ($cells[$key] ?? 0);
            }
            if ($sum !== (int) $count) {
                return false;
            }
        }

        return true;
    }

    /**
     * How many questions the bank holds per topic and bloomKey.
     *
     * @param  array<int>  $topicIds
     * @return array<int, array<string, int>>
     */
    public static function bankCaps(array $topicIds): array
    {
        $caps = [];
        $rows = DB::table('question')
            ->whereIn('topicId', $topicIds)
            ->select('topicId', 'bloomLevel', 'questionType', DB::raw('COUNT(*) as total'))
            ->groupBy('topicId', 'bloomLevel', 'questionType')
            ->get();

        foreach ($topicIds as $topicId) {
            $caps[$topicId] = [];
        }
        foreach ($rows as $row) {
            $caps[$row->topicId][self::key($row->bloomLevel, $row->questionType)] = (int) $row->total;
        }

        return $caps;
    }

    /**
     * The matrix of a saved ToS: the stored one when it still agrees with Table 2, otherwise
     * (older ToS, or a stale matrix) one worked out on the spot. Nothing is written here.
     *
     * @return array{matrix: array<int, array<string, int>>, rows: array<string, array{points: float, count: int}>, stored: bool}
     */
    public static function forTos(int $tosId): array
    {
        $topicHours = DB::table('tostopic')->where('tosId', $tosId)->pluck('contactHours', 'topicId')->all();
        ksort($topicHours);
        $topicHours = array_map('floatval', $topicHours);

        $rows = [];
        foreach (DB::table('tosbloom')->where('tosId', $tosId)->orderBy('bloomLevel')->orderBy('questionType')->get() as $row) {
            $rows[self::key($row->bloomLevel, $row->questionType)] = [
                'points' => (float) $row->pointsPerItem,
                'count' => (int) round((float) $row->generatedItemCount),
            ];
        }

        $stored = [];
        foreach (DB::table('tostopicbloom')->where('tosId', $tosId)->get() as $cell) {
            $stored[$cell->topicId][self::key($cell->bloomLevel, $cell->questionType)] = (int) $cell->itemCount;
        }

        $matrix = [];
        foreach (array_keys($topicHours) as $topicId) {
            $matrix[$topicId] = [];
            foreach (array_keys($rows) as $key) {
                $matrix[$topicId][$key] = (int) ($stored[$topicId][$key] ?? 0);
            }
        }

        $counts = array_map(fn ($r) => $r['count'], $rows);
        $isStored = ! empty($stored) && self::matchesRows($matrix, $counts);

        if (! $isStored) {
            $matrix = self::allocate($topicHours, $rows, self::bankCaps(array_keys($topicHours)));
        }

        return ['matrix' => $matrix, 'rows' => $rows, 'stored' => $isStored];
    }
}
