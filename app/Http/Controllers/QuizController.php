<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Quizzes: exams built straight from the question bank, with no Table of Specifications.
 * A quiz is stored as an exam row with a NULL tosId, its own title, and points per question.
 */
class QuizController extends Controller
{
    private const QUESTION_TYPES = ['Multiple Choice', 'True/False', 'Fill in the Blanks', 'Enumeration', 'Matching Type', 'Essay'];

    public function create(Course $course)
    {
        $this->authorizeOwner($course);

        $topics = $course->topics()->orderBy('topicName')->get();

        // Questions available per topic and type, so the form can add up only the ticked topics.
        $availableByTopic = [];
        foreach (Question::whereIn('topicId', $topics->pluck('topicId'))->selectRaw('topicId, questionType, count(*) as c')->groupBy('topicId', 'questionType')->get() as $row) {
            $availableByTopic[$row->topicId][$row->questionType] = (int) $row->c;
        }

        return view('quizzes.create', [
            'course' => $course,
            'topics' => $topics,
            'types' => self::QUESTION_TYPES,
            'availableByTopic' => $availableByTopic,
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $this->authorizeOwner($course);

        $topicIds = $course->topics()->pluck('topicId')->all();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'topicId' => ['required', 'array', 'min:1'],
            'topicId.*' => ['integer', 'in:' . implode(',', $topicIds ?: [0])],
            'itemCount' => ['required', 'array'],
            'itemCount.*' => ['nullable', 'integer', 'min:0', 'max:200'],
            'pointsPerItem' => ['required', 'array'],
            'pointsPerItem.*' => ['nullable', 'numeric', 'min:0.5', 'max:100'],
        ], [
            'title.required' => 'Required: give the quiz a title.',
            'topicId.required' => 'Choose at least one topic to draw questions from.',
        ]);

        // Only the types the teacher asked for (item count above zero).
        $plan = [];
        foreach (self::QUESTION_TYPES as $type) {
            $count = (int) ($data['itemCount'][$type] ?? 0);
            if ($count <= 0) {
                continue;
            }
            $points = (float) ($data['pointsPerItem'][$type] ?? 0);
            if ($points <= 0) {
                return back()->withInput()->withErrors(['quiz' => "Set the points per item for {$type}."]);
            }
            $plan[$type] = ['count' => $count, 'points' => $points];
        }

        if (empty($plan)) {
            return back()->withInput()->withErrors(['quiz' => 'Enter how many items you want for at least one question type.']);
        }

        // Pick random questions per type from the chosen topics; stop before saving anything if the bank is short.
        $shortages = [];
        $picked = [];
        foreach ($plan as $type => $p) {
            $pool = Question::whereIn('topicId', $data['topicId'])->where('questionType', $type)->inRandomOrder()->get(['questionId', 'bloomLevel']);
            if ($pool->count() < $p['count']) {
                $shortages[] = "{$type}: need {$p['count']}, only {$pool->count()} available";
                continue;
            }
            $picked[$type] = $pool->take($p['count']);
        }

        if (! empty($shortages)) {
            return back()->withInput()->withErrors(['quiz' => 'Not enough questions in the selected topics. ' . implode('; ', $shortages) . '.']);
        }

        $examId = DB::transaction(function () use ($course, $data, $plan, $picked) {
            $totalItems = 0;
            $totalScore = 0.0;
            $bloom = [];

            $examId = DB::table('exam')->insertGetId([
                'tosId' => null,
                'courseId' => $course->courseId,
                'title' => trim($data['title']),
                'totalItems' => 0,
                'totalScore' => 0,
                'created' => now(),
            ], 'examId');

            foreach ($picked as $type => $questions) {
                foreach ($questions as $q) {
                    DB::table('examquestion')->insert([
                        'examId' => $examId,
                        'questionId' => $q->questionId,
                        'points' => $plan[$type]['points'],
                    ]);
                    $totalItems++;
                    $totalScore += $plan[$type]['points'];
                    $bloom[$q->bloomLevel]['items'] = ($bloom[$q->bloomLevel]['items'] ?? 0) + 1;
                    $bloom[$q->bloomLevel]['score'] = ($bloom[$q->bloomLevel]['score'] ?? 0) + $plan[$type]['points'];
                }
            }

            DB::table('exam')->where('examId', $examId)->update(['totalItems' => $totalItems, 'totalScore' => $totalScore]);

            foreach ($bloom as $level => $agg) {
                DB::table('exambloomconfig')->insert([
                    'examId' => $examId,
                    'bloomLevel' => $level,
                    'numberItems' => $agg['items'],
                    'numScore' => $agg['score'],
                ]);
            }

            return $examId;
        });

        return redirect()->route('courses.exams.show', [$course, $examId])->with('status', 'Quiz created.');
    }

    private function authorizeOwner(Course $course): void
    {
        if ($course->accountId !== auth()->id()) {
            abort(403, 'You do not own this course.');
        }
    }
}
