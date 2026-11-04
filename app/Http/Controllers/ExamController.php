<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Tos;
use App\Support\TosMatrix;
use App\Services\ExamPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        // How many items each topic should contribute to each Bloom/type row (see TosMatrix).
        $distribution = TosMatrix::forTos($tos->tosId);
        $matrix = $distribution['matrix'];

        $shortages = [];
        $eligibleByRow = [];

        foreach ($bloomRows as $row) {
            $key = TosMatrix::key($row->bloomLevel, $row->questionType);

            // Eligible question ids of this row, grouped by topic.
            $byTopic = Question::whereIn('topicId', $topicIds)
                ->where('bloomLevel', $row->bloomLevel)
                ->where('questionType', $row->questionType)
                ->get(['questionId', 'topicId'])
                ->groupBy('topicId')
                ->map(fn ($g) => $g->pluck('questionId')->all())
                ->all();

            $available = array_sum(array_map('count', $byTopic));

            $eligibleByRow[] = [
                'row' => $row,
                'byTopic' => $byTopic,
                'wanted' => array_map(fn ($cells) => (int) ($cells[$key] ?? 0), $matrix),
            ];

            if ($available < $row->generatedItemCount) {
                $shortages[] = "{$row->bloomLevel} / {$row->questionType}: need {$row->generatedItemCount}, only {$available} available";
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
                $picked = collect($this->pickForRow($entry['byTopic'], $entry['wanted'], (int) $row->generatedItemCount));

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
    /**
     * Picks $need questions for one Bloom/type row: from each topic as many as the ToS distribution
     * asks for ($wanted[topicId]). If a topic's bank has fewer than that, the gap is filled from the
     * topics that still have questions left, so the exam is still generated.
     *
     * @param  array<int, array<int>>  $byTopic  eligible question ids per topic
     * @param  array<int, int>  $wanted  items wanted per topic
     * @return array<int>
     */
    private function pickForRow(array $byTopic, array $wanted, int $need): array
    {
        $picked = [];
        $left = [];

        foreach ($byTopic as $topicId => $ids) {
            $ids = collect($ids)->shuffle()->values()->all();
            $take = min((int) ($wanted[$topicId] ?? 0), count($ids));
            $picked = array_merge($picked, array_slice($ids, 0, $take));
            $left[$topicId] = array_slice($ids, $take);
        }

        while (count($picked) < $need) {
            // take from the topic with the most unused questions
            $topicId = null;
            foreach ($left as $id => $ids) {
                if (! empty($ids) && ($topicId === null || count($ids) > count($left[$topicId]))) {
                    $topicId = $id;
                }
            }
            if ($topicId === null) {
                break;
            }
            $picked[] = array_shift($left[$topicId]);
        }

        return array_slice($picked, 0, $need);
    }

    public function editFormat(Course $course, Exam $exam)
    {
        $this->authorizeOwner($course);
        $this->authorizeExamBelongsToCourse($course, $exam);

        $printMode = false;
        $pdfAvailable = ExamPdf::available();

        return view('exams.format', $this->formatViewData($exam) + compact('course', 'exam', 'printMode', 'pdfAvailable'));
    }

    /** The exam exactly as it prints, as a PDF (shown in the preview; ?download=1 saves it). */
    public function formatPdf(Request $request, Course $course, Exam $exam)
    {
        $this->authorizeOwner($course);
        $this->authorizeExamBelongsToCourse($course, $exam);

        if (! ExamPdf::available()) {
            return response('PDF engine not available. Install Google Chrome or Microsoft Edge, or set CHROME_PATH in .env.', 503);
        }

        $printMode = true;
        $pdfAvailable = false;
        $data = $this->formatViewData($exam);
        $html = view('exams.format', $data + compact('course', 'exam', 'printMode', 'pdfAvailable'))->render();

        try {
            $pdf = ExamPdf::fromHtml($html);
        } catch (\Throwable $e) {
            report($e);

            return response('Could not build the PDF preview: '.$e->getMessage(), 500);
        }

        $name = Str::slug(($data['tos']->title ?? 'exam').'-exam').'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($request->boolean('download') ? 'attachment' : 'inline').'; filename="'.$name.'"',
            'Cache-Control' => 'no-store',
        ]);
    }

    /** Data the exam paper needs, shared by the on-screen page and the PDF. */
    private function formatViewData(Exam $exam): array
    {
        $tos = Tos::find($exam->tosId);

        $defaultOrder = ['Multiple Choice', 'True/False', 'Fill in the Blanks', 'Enumeration', 'Matching Type', 'Essay'];

        $defaultDirections = self::defaultDirections();
        $directions = $this->resolveDirections($exam, $defaultDirections);

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

        return compact('tos', 'questions', 'directions', 'defaultDirections', 'presentTypes', 'columnsPerPart');
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
            'headerTemplate' => ['required', 'in:classic,formal,minimal,marsu,coe'],
            'footerTemplate' => ['required', 'in:simple,paged,minimal,marsu,coe'],
            'headerTop' => ['nullable', 'integer', 'between:0,200'],
            'headerLines' => ['nullable', 'string', 'max:600'],
            'footerLines' => ['nullable', 'string', 'max:900'],
            'watermarkImage' => ['nullable', 'image', 'max:2048'],
            'removeWatermarkImage' => ['nullable'],
            'partOrder' => ['nullable', 'array'],
            'directions' => ['nullable', 'array'],
            'directions.*' => ['nullable', 'string', 'max:600'],
        ], [], ['directions.*' => 'direction text']);

        // Directions: keep only known parts, and store only text that differs from the default.
        // An empty box (or text equal to the default) resets that part to the default wording.
        $defaults = self::defaultDirections();
        $overrides = [];
        foreach ((array) ($data['directions'] ?? []) as $type => $text) {
            if (! array_key_exists($type, $defaults)) {
                continue;
            }
            $text = trim(str_replace(["\r\n", "\r"], "\n", (string) $text));
            if ($text !== '' && $text !== $defaults[$type]) {
                $overrides[$type] = $text;
            }
        }
        $data['directions'] = $overrides ? json_encode($overrides) : null;

        // Blank "distance from top" keeps the stored value (the column is NOT NULL).
        if (! isset($data['headerTop'])) {
            unset($data['headerTop']);
        }

        // Letterhead / footer wording: one line per row; blank falls back to the built-in text.
        foreach (['headerLines', 'footerLines'] as $key) {
            $text = trim(preg_replace("/\r\n|\r/", "\n", (string) ($data[$key] ?? '')));
            $data[$key] = $text !== '' ? $text : null;
        }

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

    

    /** Default printed direction for each question type. */
    public static function defaultDirections(): array
    {
        return [
            'Multiple Choice' => 'Read each question carefully. Encircle the letter of the best answer.',
            'True/False' => 'Write TRUE if the statement is correct and FALSE if the statement is incorrect. Write your answer on the space provided before each number.',
            'Fill in the Blanks' => 'Complete each statement by writing the correct word or phrase in the blank provided.',
            'Enumeration' => 'Enumerate the required terms, concepts, or items for each question. Write your answers in the spaces provided.',
            'Matching Type' => 'Match the items in Column A with their corresponding answers in Column B. Write the letter of the correct answer on the space provided before each number.',
            'Essay' => 'Answer the following question(s) clearly and concisely. Support your answer with appropriate explanations and examples when necessary. Write your response in the space provided.',
        ];
    }

    /** Defaults with this exam's saved edits applied. Tolerates a missing column or bad JSON. */
    private function resolveDirections(Exam $exam, array $defaults): array
    {
        $saved = $exam->directions ? json_decode($exam->directions, true) : [];
        if (! is_array($saved)) {
            return $defaults;
        }
        foreach ($saved as $type => $text) {
            if (array_key_exists($type, $defaults) && is_string($text) && trim($text) !== '') {
                $defaults[$type] = $text;
            }
        }
        return $defaults;
    }

    public function show(Course $course, Exam $exam)
    {
        $this->authorizeOwner($course);
        $this->authorizeExamBelongsToCourse($course, $exam);

        $questions = DB::table('examquestion')
            ->join('question', 'question.questionId', '=', 'examquestion.questionId')
            ->leftJoin('topic', 'topic.topicId', '=', 'question.topicId')
            ->where('examquestion.examId', $exam->examId)
            ->select('question.*', 'topic.topicName')
            ->orderBy('question.bloomLevel')
            ->orderBy('question.questionType')
            ->get();

        $bloomConfig = DB::table('exambloomconfig')->where('examId', $exam->examId)->get();

        // Points per topic as this exam really adds up, next to what the ToS assigned to each topic.
        $pointsByKey = DB::table('tosbloom')->where('tosId', $exam->tosId)->get()
            ->mapWithKeys(fn ($r) => [TosMatrix::key($r->bloomLevel, $r->questionType) => (float) $r->pointsPerItem])
            ->all();

        $targets = DB::table('tostopic')
            ->join('topic', 'topic.topicId', '=', 'tostopic.topicId')
            ->where('tostopic.tosId', $exam->tosId)
            ->select('tostopic.topicId', 'topic.topicName', 'tostopic.assignedPoints')
            ->orderBy('topic.topicName')
            ->get();

        $topicSummary = [];
        foreach ($targets as $t) {
            $topicSummary[$t->topicId] = ['name' => $t->topicName, 'items' => 0, 'points' => 0.0, 'target' => (float) $t->assignedPoints];
        }
        foreach ($questions as $q) {
            $id = $q->topicId;
            if (! isset($topicSummary[$id])) {
                $topicSummary[$id] = ['name' => $q->topicName ?? '(no topic)', 'items' => 0, 'points' => 0.0, 'target' => null];
            }
            $topicSummary[$id]['items']++;
            $topicSummary[$id]['points'] += $pointsByKey[TosMatrix::key($q->bloomLevel, $q->questionType)] ?? 0;
        }

        // Exams are identified by their ToS title, not by a number.
        $tosTitle = Tos::find($exam->tosId)->title ?? 'Exam';

        return view('exams.show', compact('course', 'exam', 'questions', 'bloomConfig', 'topicSummary', 'tosTitle'));
    }

    /**
     * Questions that may replace $question in this exam: same topic, same Bloom level and
     * question type (so the ToS distribution and each topic's points still hold), and not already
     * used in the exam.
     */
    private function replacementPool(Exam $exam, Question $question)
    {
        $currentQuestionIds = DB::table('examquestion')->where('examId', $exam->examId)->pluck('questionId');

        // Same topic as the question being replaced, so each topic keeps the points the ToS gave it.
        return Question::where('topicId', $question->topicId)
            ->where('bloomLevel', $question->bloomLevel)
            ->where('questionType', $question->questionType)
            ->whereNotIn('questionId', $currentQuestionIds);
    }

    private function authorizeQuestionInExam(Exam $exam, Question $question): void
    {
        $inExam = DB::table('examquestion')
            ->where('examId', $exam->examId)
            ->where('questionId', $question->questionId)
            ->exists();

        if (! $inExam) {
            abort(404);
        }
    }

    public function replaceOptions(Course $course, Exam $exam, Question $question)
    {
        $this->authorizeOwner($course);
        $this->authorizeExamBelongsToCourse($course, $exam);
        $this->authorizeQuestionInExam($exam, $question);

        // Newest questions first, so recently added ones are easy to find.
        $candidates = $this->replacementPool($exam, $question)->orderByDesc('questionId')->get();
        $topicNames = DB::table('topic')->whereIn('topicId', $candidates->pluck('topicId')->unique())->pluck('topicName', 'topicId');
        $tosTitle = Tos::find($exam->tosId)->title ?? 'Exam';

        return view('exams.replace', compact('course', 'exam', 'question', 'candidates', 'topicNames', 'tosTitle'));
    }

    public function replace(Request $request, Course $course, Exam $exam, Question $question)
    {
        $this->authorizeOwner($course);
        $this->authorizeExamBelongsToCourse($course, $exam);
        $this->authorizeQuestionInExam($exam, $question);

        $pool = $this->replacementPool($exam, $question);

        if ($request->filled('replacementId')) {
            // The user picked a specific question: it must be one of the allowed candidates.
            $replacement = $pool->where('questionId', (int) $request->input('replacementId'))->first();

            if (! $replacement) {
                return redirect()
                    ->route('courses.exams.replace.options', [$course, $exam, $question])
                    ->withErrors(['exam' => 'That question is no longer available as a replacement. Please choose another.']);
            }
        } else {
            $replacement = $pool->inRandomOrder()->first();

            if (! $replacement) {
                return back()->withErrors(['exam' => 'No other question available in the Question Bank for that same topic, Bloom level and question type. Add one to the bank, or the topic would no longer match its ToS points.']);
            }
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