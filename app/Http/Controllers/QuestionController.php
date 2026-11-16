<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Question;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class QuestionController extends Controller
{
    private const BLOOM_LEVELS = ['Remembering', 'Understanding', 'Applying', 'Analyzing', 'Evaluating', 'Creating'];
    private const QUESTION_TYPES = ['Multiple Choice', 'True/False', 'Fill in the Blanks', 'Enumeration', 'Matching Type', 'Essay'];

   public function index(\Illuminate\Http\Request $request, Course $course, Topic $topic)
    {
        $this->authorize($course, $topic);

        $allQuestions = $topic->questions()->get();

        $counts = collect(self::QUESTION_TYPES)
            ->mapWithKeys(fn ($type) => [$type => $allQuestions->where('questionType', $type)->count()]);

        $selectedType = $request->query('type');
        if (! in_array($selectedType, self::QUESTION_TYPES, true)) {
            $selectedType = null;
        }
        $bloomFilter = $request->query('bloom');
        $search = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'newest');

        // All of the topic's questions are listed right away; the type tabs, Bloom level and
        // search box only narrow the list down.
        $questions = $allQuestions;

        if ($selectedType) {
            $questions = $questions->where('questionType', $selectedType);
        }
        if ($bloomFilter) {
            $questions = $questions->where('bloomLevel', $bloomFilter);
        }
        if ($search !== '') {
            $questions = $questions->filter(fn ($q) => mb_stripos((string) $q->content, $search) !== false
                || mb_stripos((string) $q->choices, $search) !== false);
        }

        // "Newest" is the order questions were added (ids only ever grow), so a new question is
        // always at the top, and editing an old one does not push it above the latest additions.
        $questions = match ($sort) {
            'oldest' => $questions->sortBy('questionId'),
            'az' => $questions->sortBy(fn ($q) => strtolower($q->content)),
            'za' => $questions->sortByDesc(fn ($q) => strtolower($q->content)),
            'bloom' => $questions->sortBy([['bloomLevel', 'asc'], ['questionId', 'desc']]),
            default => $questions->sortByDesc('questionId'),
        };

        $questions = $questions->values();

        return view('questions.index', [
            'course' => $course,
            'topic' => $topic,
            'counts' => $counts,
            'selectedType' => $selectedType,
            'bloomFilter' => $bloomFilter,
            'sort' => $sort,
            'search' => $search,
            'total' => $allQuestions->count(),
            'questions' => $questions,
            'bloomLevels' => self::BLOOM_LEVELS,
        ]);
    }


    public function create(Course $course, Topic $topic)
    {
        $this->authorize($course, $topic);

        return view('questions.create', [
            'course' => $course,
            'topic' => $topic,
            'bloomLevels' => self::BLOOM_LEVELS,
            'questionTypes' => self::QUESTION_TYPES,
        ]);
    }

    public function store(Request $request, Course $course, Topic $topic)
    {
        $this->authorize($course, $topic);

        $data = $request->validate([
            'bloomLevel' => ['required', 'in:' . implode(',', self::BLOOM_LEVELS)],
            'questionType' => ['required', 'in:' . implode(',', self::QUESTION_TYPES)],
            'content' => ['required', 'string'],
            'choices' => ['nullable', 'array', 'max:12'],
            'choices.*' => ['nullable', 'string', 'max:255'],
            'correctAnswer' => ['nullable', 'string'],
        ]);

        $choices = $this->cleanChoices($data);
        if ($data['questionType'] === 'Multiple Choice' && count($choices) < 2) {
            throw ValidationException::withMessages(['choices' => 'Add at least two answer choices for a Multiple Choice question.']);
        }
        if ($data['questionType'] === 'Multiple Choice' && ! in_array(trim((string) ($data['correctAnswer'] ?? '')), $choices, true)) {
            throw ValidationException::withMessages(['correctAnswer' => 'Pick the correct answer from your choices.']);
        }

        // Simple duplicate check: same content already exists under this topic
        $duplicate = Question::where('topicId', $topic->topicId)
            ->where('content', $data['content'])
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['content' => 'This exact question already exists for this topic.']);
        }

        Question::create([
            'topicId' => $topic->topicId,
            'bloomLevel' => $data['bloomLevel'],
            'questionType' => $data['questionType'],
            'content' => $data['content'],
            'choices' => $data['questionType'] === 'Multiple Choice' ? implode(',', $choices) : null,
            'correctAnswer' => $data['correctAnswer'] ?? null,
        ]);

        // Stay on the add page with a fresh form (same question type pre-chosen) and confirm with a toast.
        $next = route('courses.topics.questions.create', [$course, $topic, 'type' => $data['questionType']]);

        // The add form saves in the background and swaps itself for a fresh copy (see question-save.js), so
        // saving adds no extra browser-history entry and the Back button still leads to the question list.
        if ($request->expectsJson()) {
            $request->session()->flash('status', 'Question added.');

            return response()->json(['redirect' => $next]);
        }

        return redirect()->to($next)->with('status', 'Question added.');
    }

    public function edit(Course $course, Topic $topic, Question $question)
    {
        $this->authorize($course, $topic, $question);

        return view('questions.edit', [
            'course' => $course,
            'topic' => $topic,
            'question' => $question,
            'bloomLevels' => self::BLOOM_LEVELS,
            'questionTypes' => self::QUESTION_TYPES,
        ]);
    }

    public function update(Request $request, Course $course, Topic $topic, Question $question)
    {
        $this->authorize($course, $topic, $question);

        $data = $request->validate([
            'bloomLevel' => ['required', 'in:' . implode(',', self::BLOOM_LEVELS)],
            'questionType' => ['required', 'in:' . implode(',', self::QUESTION_TYPES)],
            'content' => ['required', 'string'],
            'choices' => ['nullable', 'array', 'max:12'],
            'choices.*' => ['nullable', 'string', 'max:255'],
            'correctAnswer' => ['nullable', 'string'],
        ]);

        $choices = $this->cleanChoices($data);
        if ($data['questionType'] === 'Multiple Choice' && count($choices) < 2) {
            return back()->withErrors(['choices' => 'Add at least two answer choices for a Multiple Choice question.'])->withInput();
        }
        $data['choices'] = $data['questionType'] === 'Multiple Choice' ? implode(',', $choices) : null;

        $question->update($data);

        return redirect()->route('courses.topics.questions.index', [$course, $topic])->with('status', 'Question updated.');
    }

    public function destroy(Request $request, Course $course, Topic $topic, Question $question)
    {
        $this->authorize($course, $topic, $question);
        $question->delete();

        // Return to the same Questions list the user was on (type, Bloom filter and sort
        // are carried by the delete form), so the updated list stays visible.
        $view = array_filter($request->only(['type', 'bloom', 'sort', 'q']), fn ($v) => is_string($v) && $v !== '');

        return redirect()
            ->to(route('courses.topics.questions.index', array_merge([$course, $topic], $view)) . '#work-results')
            ->with('status', 'Question deleted.');
    }

    /**
     * Trim the submitted choices, drop empty ones and neutralise commas
     * (choices are stored comma-joined, so a comma inside one would split it).
     */
    private function cleanChoices(array $data): array
    {
        return collect($data['choices'] ?? [])
            ->map(fn ($choice) => trim(preg_replace('/\s*,\s*/', ' ', (string) $choice)))
            ->filter(fn ($choice) => $choice !== '')
            ->values()
            ->all();
    }

    private function authorize(Course $course, Topic $topic, ?Question $question = null): void
    {
        if ($course->accountId !== auth()->id()) {
            abort(403, 'You do not own this course.');
        }
        if ($topic->courseId !== $course->courseId) {
            abort(404);
        }
        if ($question && $question->topicId !== $topic->topicId) {
            abort(404);
        }
    }
}