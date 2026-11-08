<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Tos;
use App\Support\Points;
use App\Support\TosHeading;
use App\Support\TosMatrix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TosController extends Controller
{
    // A ToS exists only for the major exams. Quizzes do not get one.
    private const EXAM_TYPES = Tos::EXAM_TYPES;
    private const SEMESTERS = ['1st semester', '2nd semester', 'Summer'];
    private const BLOOM_LEVELS = ['Remembering', 'Understanding', 'Applying', 'Analyzing', 'Evaluating', 'Creating'];
    private const QUESTION_TYPES = ['Multiple Choice', 'True/False', 'Fill in the Blanks', 'Enumeration', 'Matching Type', 'Essay'];

    private const VALIDATION_MESSAGES = [
        'examType.required' => 'Required: choose Midterm or Final.',
        'examType.in' => 'A ToS can only be made for a Midterm or Final exam.',
        'academicYear.required' => 'Required: enter the academic year, e.g. 2025-2026.',
        'academicYear.regex' => 'Academic year must look like 2025-2026.',
        'semester.required' => 'Required: choose the semester.',
        'hoursPerWeek.required' => 'Required: enter the hours per week.',
        'hoursPerWeek.min' => 'Hours per week must be greater than zero.',
        'topicId.required' => 'Required: check at least one topic in Table 1.',
        'topicId.min' => 'Required: check at least one topic in Table 1.',
        'contactHours.required' => 'Required: enter contact hours for the checked topics.',
        'bloomKey.required' => 'Required: add at least one row to Table 2.',
        'bloomKey.min' => 'Required: add at least one row to Table 2.',
    ];

    public function index(Course $course)
    {
        $this->authorizeOwner($course);
        $tosList = $course->tos()->orderBy('title')->get();

        return view('tos.index', compact('course', 'tosList'));
    }

    public function create(Course $course)
    {
        $this->authorizeOwner($course);

        $topics = $course->topics()->orderBy('topicName')->get();

        return view('tos.create', [
            'course' => $course,
            'topics' => $topics,
            'bank' => $this->bankCounts($topics),
            'bloomLevels' => self::BLOOM_LEVELS,
            'questionTypes' => self::QUESTION_TYPES,
            'examTypes' => self::EXAM_TYPES,
            'semesters' => self::SEMESTERS,
        ]);
    }

    public function edit(Course $course, Tos $tos)
    {
        $this->authorizeOwner($course);
        $this->authorizeBelongsToCourse($course, $tos);

        $topics = $course->topics()->orderBy('topicName')->get();

        $existingTopics = DB::table('tostopic')->where('tosId', $tos->tosId)->get()->keyBy('topicId');
        $existingBloom = DB::table('tosbloom')->where('tosId', $tos->tosId)->get()->keyBy(function ($row) {
            return $row->bloomLevel . '|' . $row->questionType;
        });

        $distribution = TosMatrix::forTos($tos->tosId);

        return view('tos.edit', [
            'course' => $course,
            'tos' => $tos,
            'topics' => $topics,
            'bank' => $this->bankCounts($topics),
            'existingTopics' => $existingTopics,
            'existingBloom' => $existingBloom,
            'matrix' => $distribution['matrix'],
            'matrixRows' => $distribution['rows'],
            'bloomLevels' => self::BLOOM_LEVELS,
            'questionTypes' => self::QUESTION_TYPES,
            'examTypes' => self::EXAM_TYPES,
            'semesters' => self::SEMESTERS,
        ]);
    }

    public function update(Request $request, Course $course, Tos $tos)
    {
        $this->authorizeOwner($course);
        $this->authorizeBelongsToCourse($course, $tos);

        $data = $request->validate([
            'examType' => ['required', 'in:' . implode(',', self::EXAM_TYPES)],
            'academicYear' => ['required', 'regex:/^\d{4}-\d{4}$/', function ($attribute, $value, $fail) {
                [$from, $to] = array_map('intval', explode('-', (string) $value) + [1 => 0]);
                if ($to !== $from + 1) {
                    $fail('Academic year must be two consecutive years, e.g. 2025-2026.');
                }
            }],
            'semester' => ['required', 'in:' . implode(',', self::SEMESTERS)],
            'hoursPerWeek' => ['required', 'numeric', 'min:0.5', 'max:99'],
            'topicId' => ['required', 'array', 'min:1'],
            'topicId.*' => ['integer'],
            'contactHours' => ['required', 'array'],
            'bloomKey' => ['required', 'array', 'min:1'],
            'itemCount' => ['required', 'array'],
            'pointsPerItem' => ['required', 'array'],
        ], self::VALIDATION_MESSAGES);

        $result = $this->computeAndValidate($request, $data);

        if (isset($result['error'])) {
            return back()->withErrors([($result['field'] ?? 'bloomKey') => $result['error']])->withInput();
        }

        DB::transaction(function () use ($course, $tos, $data, $result) {
            DB::table('tos')->where('tosId', $tos->tosId)->update([
                'title' => Tos::headingTitle($course),
                'examType' => $data['examType'],
                'academicYear' => $data['academicYear'],
                'semester' => $data['semester'],
                'hoursPerWeek' => $data['hoursPerWeek'],
                'totalScore' => $result['totalScore'],
            ]);

            DB::table('tostopicbloom')->where('tosId', $tos->tosId)->delete();
            DB::table('tostopic')->where('tosId', $tos->tosId)->delete();
            DB::table('tosbloom')->where('tosId', $tos->tosId)->delete();

            foreach ($result['topics'] as $topicRow) {
                DB::table('tostopic')->insert([
                    'tosId' => $tos->tosId,
                    'topicId' => $topicRow['topicId'],
                    'contactHours' => $topicRow['contactHours'],
                    'generatedWeight' => $topicRow['generatedWeight'],
                    'assignedPoints' => $topicRow['assignedPoints'],
                ]);
            }

            foreach ($result['bloom'] as $bloomRow) {
                DB::table('tosbloom')->insert([
                    'tosId' => $tos->tosId,
                    'bloomLevel' => $bloomRow['bloomLevel'],
                    'questionType' => $bloomRow['questionType'],
                    'pointsPerItem' => $bloomRow['pointsPerItem'],
                    'generatedItemCount' => $bloomRow['itemCount'],
                    'generatedScore' => $bloomRow['score'],
                ]);
            }

            $this->saveMatrix($tos->tosId, $result['matrix']);
        });

        return redirect()->route('courses.tos.index', $course)->with('status', 'ToS updated.');
    }

    public function store(Request $request, Course $course)
    {
        $this->authorizeOwner($course);

        $data = $request->validate([
            'examType' => ['required', 'in:' . implode(',', self::EXAM_TYPES)],
            'academicYear' => ['required', 'regex:/^\d{4}-\d{4}$/', function ($attribute, $value, $fail) {
                [$from, $to] = array_map('intval', explode('-', (string) $value) + [1 => 0]);
                if ($to !== $from + 1) {
                    $fail('Academic year must be two consecutive years, e.g. 2025-2026.');
                }
            }],
            'semester' => ['required', 'in:' . implode(',', self::SEMESTERS)],
            'hoursPerWeek' => ['required', 'numeric', 'min:0.5', 'max:99'],
            'topicId' => ['required', 'array', 'min:1'],
            'topicId.*' => ['integer'],
            'contactHours' => ['required', 'array'],
            'bloomKey' => ['required', 'array', 'min:1'],
            'itemCount' => ['required', 'array'],
            'pointsPerItem' => ['required', 'array'],
        ], self::VALIDATION_MESSAGES);

        $result = $this->computeAndValidate($request, $data);

        if (isset($result['error'])) {
            return back()->withErrors([($result['field'] ?? 'bloomKey') => $result['error']])->withInput();
        }

        DB::transaction(function () use ($course, $data, $result) {
            $tosId = DB::table('tos')->insertGetId([
                'courseId' => $course->courseId,
                'title' => Tos::headingTitle($course),
                'examType' => $data['examType'],
                'academicYear' => $data['academicYear'],
                'semester' => $data['semester'],
                'hoursPerWeek' => $data['hoursPerWeek'],
                'totalScore' => $result['totalScore'],
                'contactHours' => 0,
                'generatedWeight' => 0,
            ], 'tosId');

            foreach ($result['topics'] as $topicRow) {
                DB::table('tostopic')->insert([
                    'tosId' => $tosId,
                    'topicId' => $topicRow['topicId'],
                    'contactHours' => $topicRow['contactHours'],
                    'generatedWeight' => $topicRow['generatedWeight'],
                    'assignedPoints' => $topicRow['assignedPoints'],
                ]);
            }

            foreach ($result['bloom'] as $bloomRow) {
                DB::table('tosbloom')->insert([
                    'tosId' => $tosId,
                    'bloomLevel' => $bloomRow['bloomLevel'],
                    'questionType' => $bloomRow['questionType'],
                    'pointsPerItem' => $bloomRow['pointsPerItem'],
                    'generatedItemCount' => $bloomRow['itemCount'],
                    'generatedScore' => $bloomRow['score'],
                ]);
            }

            $this->saveMatrix($tosId, $result['matrix']);
        });

        return redirect()->route('courses.tos.index', $course)->with('status', 'ToS created.');
    }

    public function show(Course $course, Tos $tos)
    {
        $this->authorizeOwner($course);
        $this->authorizeBelongsToCourse($course, $tos);

        return view('tos.show', ['course' => $course, 'tos' => $tos] + $this->tableData($tos));
    }

    /** A clean, print-ready copy of the ToS: heading, Table 1 and Table 2 laid out like the paper form. */
    public function print(Course $course, Tos $tos)
    {
        $this->authorizeOwner($course);
        $this->authorizeBelongsToCourse($course, $tos);

        $data = $this->tableData($tos);

        // Topics in the order they were added to the course, numbered 1, 2, 3...
        $data['topicRows'] = $data['topicRows']->sortBy('topicId')->values();

        // Whole-number percentage per topic that adds up to exactly 100 (largest remainder).
        $weights = $data['topicRows']->pluck('generatedWeight', 'topicId')->map(fn ($w) => (float) $w)->all();
        $weightSum = array_sum($weights);
        $topicPercent = array_fill_keys(array_keys($weights), 0);
        if ($weightSum > 0) {
            $exactPct = array_map(fn ($w) => $w / $weightSum * 100, $weights);
            $given = 0;
            foreach ($exactPct as $id => $v) {
                $topicPercent[$id] = (int) floor($v + 1e-9);
                $given += $topicPercent[$id];
            }
            $byFraction = array_keys($exactPct);
            usort($byFraction, fn ($x, $y) => ($exactPct[$y] - floor($exactPct[$y])) <=> ($exactPct[$x] - floor($exactPct[$x])));
            for ($i = 0; $given < 100 && $i < count($byFraction); $i++, $given++) {
                $topicPercent[$byFraction[$i]]++;
            }
        }
        $data['topicPercent'] = $topicPercent;

        // Test numbering follows the usual exam part order; items run 1..n within each test,
        // continuing from one Bloom level to the next for the same question type.
        $typeOrder = array_values(array_intersect(self::QUESTION_TYPES, $data['bloomRows']->pluck('questionType')->unique()->all()));
        $testNumber = array_flip($typeOrder);
        $nextItem = array_fill_keys($typeOrder, 1);

        $levels = [];
        foreach (self::BLOOM_LEVELS as $level) {
            $levels[$level] = ['rows' => [], 'items' => 0, 'percent' => 0];
        }

        foreach ($data['bloomRows']->sortBy(fn ($r) => [array_search($r->bloomLevel, self::BLOOM_LEVELS), $testNumber[$r->questionType]]) as $row) {
            $count = (int) round((float) $row->generatedItemCount);
            if ($count <= 0 || ! isset($levels[$row->bloomLevel])) {
                continue;
            }
            $from = $nextItem[$row->questionType];
            $to = $from + $count - 1;
            $nextItem[$row->questionType] = $to + 1;

            $levels[$row->bloomLevel]['rows'][] = [
                'type' => $row->questionType,
                'numbers' => 'Test ' . TosHeading::roman($testNumber[$row->questionType] + 1) . ' ' . $from . '-' . $to,
                'count' => $count,
            ];
            $levels[$row->bloomLevel]['items'] += $count;
        }

        $totalItems = array_sum(array_column($levels, 'items'));

        // Whole-number percentages that add up to exactly 100 (largest remainder).
        if ($totalItems > 0) {
            $exact = [];
            $assigned = 0;
            foreach ($levels as $name => $level) {
                $exact[$name] = $level['items'] / $totalItems * 100;
                $levels[$name]['percent'] = (int) floor($exact[$name]);
                $assigned += $levels[$name]['percent'];
            }
            $order = array_keys($levels);
            usort($order, fn ($x, $y) => ($exact[$y] - floor($exact[$y])) <=> ($exact[$x] - floor($exact[$x])));
            for ($i = 0; $assigned < 100 && $i < count($order); $i++, $assigned++) {
                if ($exact[$order[$i]] > 0) {
                    $levels[$order[$i]]['percent']++;
                }
            }
        }

        return view('tos.print', ['course' => $course, 'tos' => $tos, 'levels' => $levels, 'totalItems' => $totalItems] + $data);
    }

    /**
     * The two ToS tables as they should read. Points per topic are what the topic x Bloom/type
     * distribution really adds up to, so this agrees with the generated exam (older ToS get their
     * distribution computed on the fly).
     */
    private function tableData(Tos $tos): array
    {
        $distribution = TosMatrix::forTos($tos->tosId);
        $pointsByKey = array_map(fn ($r) => $r['points'], $distribution['rows']);
        $pointsByTopic = TosMatrix::topicPoints($distribution['matrix'], $pointsByKey);

        $topicRows = DB::table('tostopic')
            ->join('topic', 'topic.topicId', '=', 'tostopic.topicId')
            ->where('tostopic.tosId', $tos->tosId)
            ->select('tostopic.topicId', 'topic.topicName', 'tostopic.contactHours', 'tostopic.generatedWeight', 'tostopic.assignedPoints')
            ->orderBy('topic.topicName')
            ->get();

        foreach ($topicRows as $row) {
            if (array_key_exists($row->topicId, $pointsByTopic)) {
                $row->assignedPoints = $pointsByTopic[$row->topicId];
            }
        }

        $bloomRows = DB::table('tosbloom')
            ->where('tosId', $tos->tosId)
            ->orderBy('bloomLevel')
            ->orderBy('questionType')
            ->get();

        return compact('topicRows', 'bloomRows');
    }

    public function destroy(Course $course, Tos $tos)
    {
        $this->authorizeOwner($course);
        $this->authorizeBelongsToCourse($course, $tos);

        DB::transaction(function () use ($tos) {
            DB::table('tostopicbloom')->where('tosId', $tos->tosId)->delete();
            DB::table('tostopic')->where('tosId', $tos->tosId)->delete();
            DB::table('tosbloom')->where('tosId', $tos->tosId)->delete();
            DB::table('tos')->where('tosId', $tos->tosId)->delete();
        });

        return redirect()->route('courses.tos.index', $course)->with('status', 'ToS deleted.');
    }

    /**
     * Splits the total score across topics in proportion to their contact hours, in WHOLE points
     * (largest-remainder method), so the points always add up exactly to the total score.
     * If the total score itself is not a whole number, points keep 2 decimals as before.
     *
     * @param  array<int|string, float>  $hoursByKey  contact hours keyed by topic id
     * @return array<int|string, int|float>
     */
    public static function allocatePoints(array $hoursByKey, float $totalScore): array
    {
        $totalHours = array_sum($hoursByKey);
        $result = array_fill_keys(array_keys($hoursByKey), 0);

        if ($totalHours <= 0) {
            return $result;
        }

        if (abs($totalScore - round($totalScore)) > 0.00001) {
            $running = 0.0;
            $keys = array_keys($hoursByKey);
            $last = end($keys);
            foreach ($hoursByKey as $key => $hours) {
                $points = $key === $last
                    ? round($totalScore - $running, 2)
                    : round(($hours / $totalHours) * $totalScore, 2);
                $running += $points;
                $result[$key] = $points;
            }

            return $result;
        }

        $target = (int) round($totalScore);
        $remainders = [];
        $assigned = 0;

        foreach ($hoursByKey as $key => $hours) {
            $exact = ($hours / $totalHours) * $target;
            $whole = (int) floor($exact + 1e-9);
            $result[$key] = $whole;
            $assigned += $whole;
            $remainders[$key] = $exact - $whole;
        }

        // Hand the leftover points to the topics with the biggest fractions (ties: more contact hours first).
        $order = array_keys($hoursByKey);
        usort($order, function ($a, $b) use ($remainders, $hoursByKey) {
            return [$remainders[$b], $hoursByKey[$b]] <=> [$remainders[$a], $hoursByKey[$a]];
        });

        for ($i = 0; $assigned < $target && $i < count($order); $i++, $assigned++) {
            $result[$order[$i]]++;
        }

        return $result;
    }

    private function computeAndValidate(Request $request, array $data): array
    {
        $topicIds = $data['topicId'];
        $contactHoursInput = $request->input('contactHours', []);

        $totalContactHours = 0;
        foreach ($topicIds as $topicId) {
            $totalContactHours += (float) ($contactHoursInput[$topicId] ?? 0);
        }

        if ($totalContactHours <= 0) {
            return ['error' => 'Total contact hours must be greater than zero.', 'field' => 'contactHours'];
        }

        $topics = [];
        $topicIdsSorted = $topicIds;
        sort($topicIdsSorted);

        $hoursById = [];
        foreach ($topicIdsSorted as $topicId) {
            $hoursById[$topicId] = (float) ($contactHoursInput[$topicId] ?? 0);
        }

        foreach ($topicIdsSorted as $topicId) {
            $hours = $hoursById[$topicId];
            $topics[] = [
                'topicId' => $topicId,
                'contactHours' => $hours,
                'generatedWeight' => round(($hours / $totalContactHours) * 100, 2),
                'assignedPoints' => 0, // filled in below, from the topic x Bloom/type distribution
            ];
        }

        $bloomKeys = $data['bloomKey'];
        $itemCounts = $request->input('itemCount', []);
        $pointsPerItemInput = $request->input('pointsPerItem', []);

        $bloom = [];
        $bloomScoreTotal = 0;

        foreach ($bloomKeys as $key) {
            [$level, $type] = explode('|', $key, 2);
            $count = (int) ($itemCounts[$key] ?? 0);
            $pointsPerItem = (float) ($pointsPerItemInput[$key] ?? 0);

            if ($count <= 0 || $pointsPerItem <= 0) {
                continue;
            }

            $score = round($count * $pointsPerItem, 2);
            $bloomScoreTotal += $score;

            $bloom[] = [
                'bloomLevel' => $level,
                'questionType' => $type,
                'pointsPerItem' => $pointsPerItem,
                'itemCount' => $count,
                'score' => $score,
            ];
        }

        if (empty($bloom)) {
            return ['error' => 'Select at least one Bloom level/question type with an item count and points per item greater than zero.', 'field' => 'bloomKey'];
        }

        $bloomScoreTotal = round($bloomScoreTotal, 2);

        // The total score is not asked for: it is simply the sum of the Table 2 rows.

        // How many items of each Bloom/type row belong to each topic. Normally worked out automatically
        // (closest to each topic's contact-hours share); the user may supply their own on the edit page.
        $rowsByKey = [];
        foreach ($bloom as $b) {
            $rowsByKey[TosMatrix::key($b['bloomLevel'], $b['questionType'])] = [
                'points' => $b['pointsPerItem'],
                'count' => $b['itemCount'],
            ];
        }

        if ($request->boolean('customMatrix')) {
            $matrix = [];
            $given = (array) $request->input('matrix', []);
            foreach ($hoursById as $topicId => $hours) {
                foreach ($rowsByKey as $key => $row) {
                    $value = $given[$topicId][$key] ?? 0;
                    if (! is_numeric($value) || $value < 0 || floor((float) $value) != (float) $value) {
                        return ['error' => 'Topic distribution: item counts must be whole numbers, zero or more.', 'field' => 'matrix'];
                    }
                    $matrix[$topicId][$key] = (int) $value;
                }
            }

            foreach ($rowsByKey as $key => $row) {
                $sum = 0;
                foreach ($matrix as $cells) {
                    $sum += $cells[$key];
                }
                if ($sum !== $row['count']) {
                    [$level, $type] = explode('|', $key, 2);

                    return ['error' => "Topic distribution: {$level} / {$type} has {$sum} item(s) across topics but Table 2 says {$row['count']}. Fix the counts, or untick \"Use my own distribution\" to let the system split them.", 'field' => 'matrix'];
                }
            }
        } else {
            $matrix = TosMatrix::allocate($hoursById, $rowsByKey, TosMatrix::bankCaps(array_map('intval', array_keys($hoursById))));
        }

        $pointsByKey = array_map(fn ($r) => $r['points'], $rowsByKey);
        $pointsByTopic = TosMatrix::topicPoints($matrix, $pointsByKey);
        foreach ($topics as &$topicRow) {
            $topicRow['assignedPoints'] = $pointsByTopic[$topicRow['topicId']] ?? 0;
        }
        unset($topicRow);

        return ['topics' => $topics, 'bloom' => $bloom, 'matrix' => $matrix, 'totalScore' => $bloomScoreTotal];
    }

    /** Stores the topic x Bloom/type item counts (zero cells are left out). */
    private function saveMatrix(int $tosId, array $matrix): void
    {
        foreach ($matrix as $topicId => $cells) {
            foreach ($cells as $key => $count) {
                if ($count <= 0) {
                    continue;
                }
                [$level, $type] = explode('|', $key, 2);
                DB::table('tostopicbloom')->insert([
                    'tosId' => $tosId,
                    'topicId' => $topicId,
                    'bloomLevel' => $level,
                    'questionType' => $type,
                    'itemCount' => $count,
                ]);
            }
        }
    }

    /**
     * How many questions the Question Bank holds right now, per topic and "Bloom level|Question type".
     * The ToS form uses this to show teachers how many items they can plan before generating an exam.
     *
     * @param  \Illuminate\Support\Collection|iterable  $topics  the course's topics
     * @return array<int, array<string, int>>
     */
    private function bankCounts($topics): array
    {
        $ids = collect($topics)->pluck('topicId')->map(fn ($id) => (int) $id)->all();

        return $ids ? TosMatrix::bankCaps($ids) : [];
    }

    private function authorizeOwner(Course $course): void
    {
        if ($course->accountId !== auth()->id()) {
            abort(403, 'You do not own this course.');
        }
    }

    private function authorizeBelongsToCourse(Course $course, Tos $tos): void
    {
        if ($tos->courseId !== $course->courseId) {
            abort(404);
        }
    }
}