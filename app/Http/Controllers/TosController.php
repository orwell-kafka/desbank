<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Tos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TosController extends Controller
{
    private const EXAM_TYPES = ['Midterm', 'Final', 'Quarterly Quiz', 'Long Quiz'];
    private const BLOOM_LEVELS = ['Remembering', 'Understanding', 'Applying', 'Analyzing', 'Evaluating', 'Creating'];
    private const QUESTION_TYPES = ['Multiple Choice', 'True/False', 'Fill in the Blanks', 'Enumeration', 'Matching Type', 'Essay'];

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
            'bloomLevels' => self::BLOOM_LEVELS,
            'questionTypes' => self::QUESTION_TYPES,
            'examTypes' => self::EXAM_TYPES,
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

        return view('tos.edit', [
            'course' => $course,
            'tos' => $tos,
            'topics' => $topics,
            'existingTopics' => $existingTopics,
            'existingBloom' => $existingBloom,
            'bloomLevels' => self::BLOOM_LEVELS,
            'questionTypes' => self::QUESTION_TYPES,
            'examTypes' => self::EXAM_TYPES,
        ]);
    }

    public function update(Request $request, Course $course, Tos $tos)
    {
        $this->authorizeOwner($course);
        $this->authorizeBelongsToCourse($course, $tos);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'examType' => ['required', 'in:' . implode(',', self::EXAM_TYPES)],
            'totalScore' => ['required', 'numeric', 'min:1'],
            'topicId' => ['required', 'array', 'min:1'],
            'topicId.*' => ['integer'],
            'contactHours' => ['required', 'array'],
            'bloomKey' => ['required', 'array', 'min:1'],
            'itemCount' => ['required', 'array'],
            'pointsPerItem' => ['required', 'array'],
        ]);

        $result = $this->computeAndValidate($request, $data);

        if (isset($result['error'])) {
            return back()->withErrors(['totalScore' => $result['error']])->withInput();
        }

        DB::transaction(function () use ($tos, $data, $result) {
            DB::table('tos')->where('tosId', $tos->tosId)->update([
                'title' => $data['title'],
                'examType' => $data['examType'],
                'totalScore' => $data['totalScore'],
            ]);

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
        });

        return redirect()->route('courses.tos.index', $course)->with('status', 'ToS updated.');
    }

    public function store(Request $request, Course $course)
    {
        $this->authorizeOwner($course);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'examType' => ['required', 'in:' . implode(',', self::EXAM_TYPES)],
            'totalScore' => ['required', 'numeric', 'min:1'],
            'topicId' => ['required', 'array', 'min:1'],
            'topicId.*' => ['integer'],
            'contactHours' => ['required', 'array'],
            'bloomKey' => ['required', 'array', 'min:1'],
            'itemCount' => ['required', 'array'],
            'pointsPerItem' => ['required', 'array'],
        ]);

        $result = $this->computeAndValidate($request, $data);

        if (isset($result['error'])) {
            return back()->withErrors(['totalScore' => $result['error']])->withInput();
        }

        DB::transaction(function () use ($course, $data, $result) {
            $tosId = DB::table('tos')->insertGetId([
                'courseId' => $course->courseId,
                'title' => $data['title'],
                'examType' => $data['examType'],
                'totalScore' => $data['totalScore'],
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
        });

        return redirect()->route('courses.tos.index', $course)->with('status', 'ToS created.');
    }

    public function show(Course $course, Tos $tos)
    {
        $this->authorizeOwner($course);
        $this->authorizeBelongsToCourse($course, $tos);

        $topicRows = DB::table('tostopic')
            ->join('topic', 'topic.topicId', '=', 'tostopic.topicId')
            ->where('tostopic.tosId', $tos->tosId)
            ->select('topic.topicName', 'tostopic.contactHours', 'tostopic.generatedWeight', 'tostopic.assignedPoints')
            ->orderBy('topic.topicName')
            ->get();

        $bloomRows = DB::table('tosbloom')
            ->where('tosId', $tos->tosId)
            ->orderBy('bloomLevel')
            ->orderBy('questionType')
            ->get();

        return view('tos.show', compact('course', 'tos', 'topicRows', 'bloomRows'));
    }

    public function destroy(Course $course, Tos $tos)
    {
        $this->authorizeOwner($course);
        $this->authorizeBelongsToCourse($course, $tos);

        DB::transaction(function () use ($tos) {
            DB::table('tostopic')->where('tosId', $tos->tosId)->delete();
            DB::table('tosbloom')->where('tosId', $tos->tosId)->delete();
            DB::table('tos')->where('tosId', $tos->tosId)->delete();
        });

        return redirect()->route('courses.tos.index', $course)->with('status', 'ToS deleted.');
    }

    private function computeAndValidate(Request $request, array $data): array
    {
        $totalScore = round((float) $data['totalScore'], 2);

        $topicIds = $data['topicId'];
        $contactHoursInput = $request->input('contactHours', []);

        $totalContactHours = 0;
        foreach ($topicIds as $topicId) {
            $totalContactHours += (float) ($contactHoursInput[$topicId] ?? 0);
        }

        if ($totalContactHours <= 0) {
            return ['error' => 'Total contact hours must be greater than zero.'];
        }

        $topics = [];
        $pointsRunningTotal = 0;
        $topicIdsSorted = $topicIds;
        sort($topicIdsSorted);

        foreach ($topicIdsSorted as $i => $topicId) {
            $hours = (float) ($contactHoursInput[$topicId] ?? 0);
            $weight = round(($hours / $totalContactHours) * 100, 2);
            $points = round(($weight / 100) * $totalScore, 2);

            if ($i === count($topicIdsSorted) - 1) {
                $points = round($totalScore - $pointsRunningTotal, 2);
            } else {
                $pointsRunningTotal += $points;
            }

            $topics[] = [
                'topicId' => $topicId,
                'contactHours' => $hours,
                'generatedWeight' => $weight,
                'assignedPoints' => $points,
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
            return ['error' => 'Select at least one Bloom level/question type with an item count and points per item greater than zero.'];
        }

        $bloomScoreTotal = round($bloomScoreTotal, 2);

        if (abs($bloomScoreTotal - $totalScore) > 0.01) {
            return ['error' => "Bloom's Taxonomy total score ({$bloomScoreTotal}) does not match the ToS total score ({$totalScore}). Adjust item counts or points per item and try again."];
        }

        return ['topics' => $topics, 'bloom' => $bloom];
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