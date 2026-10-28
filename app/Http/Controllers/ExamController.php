<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Tos;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function index(Course $course)
    {
        $this->authorizeOwner($course);

        $exams = Exam::whereIn('tosId', $course->tos()->pluck('tosId'))
            ->orderBy('examId', 'desc')
            ->get();

        $tosTitles = $course->tos()->pluck('title', 'tosId');
        foreach ($exams as $exam) {
            $exam->tosTitle = $tosTitles[$exam->tosId] ?? '(unknown ToS)';
        }

        return view('exams.index', compact('course', 'exams'));
    }

    public function generate(Course $course, Tos $tos)
    {
        $this->authorizeOwner($course);
        $this->authorizeTosBelongsToCourse($course, $tos);

        $topicIds = DB::table('tostopic')->where('tosId', $tos->tosId)->pluck('topicId');
        $bloomRows = DB::table('tosbloom')->where('tosId', $tos->tosId)->get();

        $shortages = [];
        $eligibleByRow = [];

        foreach ($bloomRows as $row) {
            $eligibleIds = Question::whereIn('topicId', $topicIds)
                ->where('bloomLevel', $row->bloomLevel)
                ->where('questionType', $row->questionType)
                ->pluck('questionId')
                ->all();

            $eligibleByRow[] = ['row' => $row, 'ids' => $eligibleIds];

            if (count($eligibleIds) < $row->generatedItemCount) {
                $shortages[] = "{$row->bloomLevel} / {$row->questionType}: need {$row->generatedItemCount}, only " . count($eligibleIds) . ' available';
            }
        }

        if (! empty($shortages)) {
            return back()->withErrors([
                'exam' => 'Not enough questions in the Question Bank to generate this exam. Add more questions for: ' . implode('; ', $shortages),
            ]);
        }

        $examId = null;
        $bloomAgg = [];

        DB::transaction(function () use ($tos, $eligibleByRow, &$examId, &$bloomAgg) {
            $examId = DB::table('exam')->insertGetId([
                'tosId' => $tos->tosId,
                'totalItems' => 0,
                'totalScore' => $tos->totalScore,
                'created' => now(),
            ], 'examId');

            $totalItems = 0;

            foreach ($eligibleByRow as $entry) {
                $row = $entry['row'];
                $picked = collect($entry['ids'])->shuffle()->take((int) $row->generatedItemCount);

                foreach ($picked as $questionId) {
                    DB::table('examquestion')->insert([
                        'examId' => $examId,
                        'questionId' => $questionId,
                    ]);
                }

                $totalItems += $picked->count();

                if (! isset($bloomAgg[$row->bloomLevel])) {
                    $bloomAgg[$row->bloomLevel] = ['items' => 0, 'score' => 0];
                }
                $bloomAgg[$row->bloomLevel]['items'] += $picked->count();
                $bloomAgg[$row->bloomLevel]['score'] += $picked->count() * $row->pointsPerItem;
            }

            DB::table('exam')->where('examId', $examId)->update(['totalItems' => $totalItems]);

            foreach ($bloomAgg as $level => $agg) {
                DB::table('exambloomconfig')->insert([
                    'examId' => $examId,
                    'bloomLevel' => $level,
                    'numberItems' => $agg['items'],
                    'numScore' => $agg['score'],
                ]);
            }
        });

        return redirect()->route('courses.exams.show', [$course, $examId])->with('status', 'Exam generated.');
    }
    public function editFormat(Course $course, Exam $exam)
    {
        $this->authorizeOwner($course);
        $this->authorizeExamBelongsToCourse($course, $exam);

        $tos = Tos::find($exam->tosId);

        $defaultOrder = ['Multiple Choice', 'True/False', 'Fill in the Blanks', 'Enumeration', 'Matching Type', 'Essay'];

        $directions = [
            'Multiple Choice' => 'Read each question carefully. Encircle the letter of the best answer.',
            'True/False' => 'Write TRUE if the statement is correct and FALSE if the statement is incorrect. Write your answer on the space provided before each number.',
            'Fill in the Blanks' => 'Complete each statement by writing the correct word or phrase in the blank provided.',
            'Enumeration' => 'Enumerate the required terms, concepts, or items for each question. Write your answers in the spaces provided.',
            'Matching Type' => 'Match the items in Column A with their corresponding answers in Column B. Write the letter of the correct answer on the space provided before each number.',
            'Essay' => 'Answer the following question(s) clearly and concisely. Support your answer with appropriate explanations and examples when necessary. Write your response in the space provided.',
        ];

        $questionsRaw = DB::table('examquestion')
            ->join('question', 'question.questionId', '=', 'examquestion.questionId')
            ->where('examquestion.examId', $exam->examId)
            ->select('question.*')
            ->orderBy('question.questionId')
            ->get()
            ->groupBy('questionType');

        $presentTypes = collect($defaultOrder)->filter(fn ($type) => $questionsRaw->has($type))->values();

        // Apply saved order if present, keeping only types actually in this exam,
        // then append any present types missing from the saved order at the end.
        if ($exam->partOrder) {
            $savedOrder = collect(explode(',', $exam->partOrder))->filter(fn ($t) => $presentTypes->contains($t));
            $remaining = $presentTypes->diff($savedOrder);
            $finalOrder = $savedOrder->merge($remaining)->values();
        } else {
            $finalOrder = $presentTypes;
        }

        $questions = $finalOrder->mapWithKeys(fn ($type) => [$type => $questionsRaw[$type]]);

        $columnsPerPart = $exam->columnsPerPart ? json_decode($exam->columnsPerPart, true) : [];
        return view('exams.format', compact('course', 'exam', 'tos', 'questions', 'directions', 'presentTypes', 'columnsPerPart'));
    }

public function answerKey(Course $course, Exam $exam)
    {
        $this->authorizeOwner($course);
        $this->authorizeExamBelongsToCourse($course, $exam);

        $tos = Tos::find($exam->tosId);

        $defaultOrder = ['Multiple Choice', 'True/False', 'Fill in the Blanks', 'Enumeration', 'Matching Type', 'Essay'];
        $letters = ['A','B','C','D','E','F','G','H','I','J','K','L'];

        $questionsRaw = DB::table('examquestion')
            ->join('question', 'question.questionId', '=', 'examquestion.questionId')
            ->where('examquestion.examId', $exam->examId)
            ->select('question.*')
            ->orderBy('question.questionId')
            ->get()
            ->groupBy('questionType');

        $presentTypes = collect($defaultOrder)->filter(fn ($type) => $questionsRaw->has($type))->values();

        if ($exam->partOrder) {
            $savedOrder = collect(explode(',', $exam->partOrder))->filter(fn ($t) => $presentTypes->contains($t));
            $remaining = $presentTypes->diff($savedOrder);
            $finalOrder = $savedOrder->merge($remaining)->values();
        } else {
            $finalOrder = $presentTypes;
        }

        $sections = [];

        foreach ($finalOrder as $type) {
            $group = $questionsRaw[$type];
            $items = [];

            if ($type === 'Matching Type') {
                mt_srand($exam->examId);
                $pool = $group->sortBy('questionId')->pluck('correctAnswer')->values()->all();
                shuffle($pool);
                $usedIndexes = [];

                $n = 1;
                foreach ($group->sortBy('questionId') as $question) {
                    $letter = '?';
                    foreach ($pool as $idx => $ans) {
                        if (! in_array($idx, $usedIndexes) && $ans === $question->correctAnswer) {
                            $letter = $letters[$idx] ?? '?';
                            $usedIndexes[] = $idx;
                            break;
                        }
                    }
                    $items[] = ['number' => $n, 'answer' => $letter, 'question' => \Illuminate\Support\Str::limit($question->content, 60)];
                    $n++;
                }
            } elseif ($type === 'Multiple Choice') {
                $n = 1;
                foreach ($group as $question) {
                    $answerLetter = '?';
                    if ($question->choices) {
                        foreach (explode(',', $question->choices) as $i => $choice) {
                            if (trim($choice) === trim((string) $question->correctAnswer)) {
                                $answerLetter = $letters[$i] ?? '?';
                                break;
                            }
                        }
                    }
                    $items[] = ['number' => $n, 'answer' => $answerLetter, 'question' => \Illuminate\Support\Str::limit($question->content, 60)];
                    $n++;
                }
            } else {
                $n = 1;
                foreach ($group as $question) {
                    $answer = $type === 'Essay'
                        ? ($question->correctAnswer ? $question->correctAnswer : '(manually graded — see rubric)')
                        : $question->correctAnswer;
                    $items[] = ['number' => $n, 'answer' => $answer, 'question' => \Illuminate\Support\Str::limit($question->content, 60)];
                    $n++;
                }
            }

            $sections[$type] = $items;
        }

        return view('exams.answer-key', compact('course', 'exam', 'tos', 'sections'));
    }

public function updateFormat(\Illuminate\Http\Request $request, Course $course, Exam $exam)
    {
        $this->authorizeOwner($course);
        $this->authorizeExamBelongsToCourse($course, $exam);

        $data = $request->validate([
            'paperSize' => ['required', 'in:Letter,Legal'],
            'schoolName' => ['nullable', 'string', 'max:150'],
            'department' => ['nullable', 'string', 'max:150'],
            'footerText' => ['nullable', 'string', 'max:255'],
            'watermarkText' => ['nullable', 'string', 'max:100'],
            'columns' => ['nullable', 'array'],
            'headerTemplate' => ['required', 'in:classic,formal,minimal'],
            'footerTemplate' => ['required', 'in:simple,paged,minimal'],
            'watermarkImage' => ['nullable', 'image', 'max:2048'],
            'removeWatermarkImage' => ['nullable'],
            'partOrder' => ['nullable', 'array'],
        ]);

        if (! empty($data['partOrder'])) {
            $data['partOrder'] = implode(',', array_unique($data['partOrder']));
        } else {
            unset($data['partOrder']);
        }

        if (! empty($data['columns'])) {
            $data['columnsPerPart'] = json_encode($data['columns']);
        }
        unset($data['columns']);

        unset($data['watermarkImage'], $data['removeWatermarkImage']);

        if ($request->boolean('removeWatermarkImage')) {
            $data['watermarkImagePath'] = null;
        } elseif ($request->hasFile('watermarkImage')) {
            $data['watermarkImagePath'] = $request->file('watermarkImage')->store('exam-images', 'public');
        }

        DB::table('exam')->where('examId', $exam->examId)->update($data);

        return redirect()->route('courses.exams.format', [$course, $exam])->with('status', 'Preview updated.');
    }

    

    public function show(Course $course, Exam $exam)
    {
        $this->authorizeOwner($course);
        $this->authorizeExamBelongsToCourse($course, $exam);

        $questions = DB::table('examquestion')
            ->join('question', 'question.questionId', '=', 'examquestion.questionId')
            ->where('examquestion.examId', $exam->examId)
            ->select('question.*')
            ->orderBy('question.bloomLevel')
            ->orderBy('question.questionType')
            ->get();

        $bloomConfig = DB::table('exambloomconfig')->where('examId', $exam->examId)->get();

        return view('exams.show', compact('course', 'exam', 'questions', 'bloomConfig'));
    }

    public function replace(Course $course, Exam $exam, Question $question)
    {
        $this->authorizeOwner($course);
        $this->authorizeExamBelongsToCourse($course, $exam);

        $tos = Tos::findOrFail($exam->tosId);
        $topicIds = DB::table('tostopic')->where('tosId', $tos->tosId)->pluck('topicId');

        $currentQuestionIds = DB::table('examquestion')->where('examId', $exam->examId)->pluck('questionId');

        $replacement = Question::whereIn('topicId', $topicIds)
            ->where('bloomLevel', $question->bloomLevel)
            ->where('questionType', $question->questionType)
            ->whereNotIn('questionId', $currentQuestionIds)
            ->inRandomOrder()
            ->first();

        if (! $replacement) {
            return back()->withErrors(['exam' => 'No other question available in the Question Bank for that same Bloom level/question type slot.']);
        }

        DB::table('examquestion')
            ->where('examId', $exam->examId)
            ->where('questionId', $question->questionId)
            ->update(['questionId' => $replacement->questionId]);

        return redirect()->route('courses.exams.show', [$course, $exam])->with('status', 'Question replaced.');
    }

    public function destroy(Course $course, Exam $exam)
    {
        $this->authorizeOwner($course);
        $this->authorizeExamBelongsToCourse($course, $exam);

        DB::transaction(function () use ($exam) {
            DB::table('examquestion')->where('examId', $exam->examId)->delete();
            DB::table('exambloomconfig')->where('examId', $exam->examId)->delete();
            DB::table('exam')->where('examId', $exam->examId)->delete();
        });

        return redirect()->route('courses.exams.index', $course)->with('status', 'Exam deleted.');
    }

    private function authorizeOwner(Course $course): void
    {
        if ($course->accountId !== auth()->id()) {
            abort(403, 'You do not own this course.');
        }
    }

    private function authorizeTosBelongsToCourse(Course $course, Tos $tos): void
    {
        if ($tos->courseId !== $course->courseId) {
            abort(404);
        }
    }

    private function authorizeExamBelongsToCourse(Course $course, Exam $exam): void
    {
        $tos = Tos::find($exam->tosId);
        if (! $tos || $tos->courseId !== $course->courseId) {
            abort(404);
        }
    }
}