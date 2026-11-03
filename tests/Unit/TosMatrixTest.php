<?php

namespace Tests\Unit;

use App\Support\TosMatrix;
use PHPUnit\Framework\TestCase;

class TosMatrixTest extends TestCase
{
    private function pointsByKey(array $rows): array
    {
        return array_map(fn ($r) => $r['points'], $rows);
    }

    public function test_every_row_is_fully_distributed_and_total_is_preserved(): void
    {
        $hours = [1 => 3, 2 => 2, 3 => 1];
        $rows = [
            'Remembering|Multiple Choice' => ['points' => 1, 'count' => 40],
            'Understanding|True/False' => ['points' => 1, 'count' => 20],
            'Applying|Enumeration' => ['points' => 2, 'count' => 10],
            'Analyzing|Essay' => ['points' => 10, 'count' => 2],
        ];

        $matrix = TosMatrix::allocate($hours, $rows);

        $counts = array_map(fn ($r) => $r['count'], $rows);
        $this->assertTrue(TosMatrix::matchesRows($matrix, $counts));

        $points = TosMatrix::topicPoints($matrix, $this->pointsByKey($rows));
        $this->assertEquals(100, array_sum($points));
        $this->assertEquals([1 => 50, 2 => 33, 3 => 17], $points);
    }

    public function test_bank_limits_are_respected_when_possible(): void
    {
        $hours = [1 => 1, 2 => 1];
        $rows = ['Analyzing|Essay' => ['points' => 5, 'count' => 2]];

        $matrix = TosMatrix::allocate($hours, $rows, [1 => ['Analyzing|Essay' => 0], 2 => ['Analyzing|Essay' => 2]]);

        $this->assertSame(0, $matrix[1]['Analyzing|Essay']);
        $this->assertSame(2, $matrix[2]['Analyzing|Essay']);
    }

    public function test_random_scenarios_keep_invariants(): void
    {
        mt_srand(11);
        $values = [0.5, 1, 1, 2, 3, 5, 10];

        for ($i = 0; $i < 300; $i++) {
            $hours = [];
            for ($t = 1; $t <= mt_rand(2, 7); $t++) {
                $hours[$t] = mt_rand(1, 10) / 2;
            }
            $rows = [];
            for ($r = 0; $r < mt_rand(2, 6); $r++) {
                $rows["L{$r}|T{$r}"] = ['points' => $values[array_rand($values)], 'count' => mt_rand(1, 12)];
            }

            $matrix = TosMatrix::allocate($hours, $rows);

            $this->assertTrue(TosMatrix::matchesRows($matrix, array_map(fn ($r) => $r['count'], $rows)));
            $total = array_sum(array_map(fn ($r) => $r['points'] * $r['count'], $rows));
            $this->assertEqualsWithDelta($total, array_sum(TosMatrix::topicPoints($matrix, $this->pointsByKey($rows))), 0.001);
        }
    }
}
