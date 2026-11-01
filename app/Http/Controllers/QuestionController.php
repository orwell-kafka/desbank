<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Question;
use App\Models\Topic;
use Illuminate\Http\Request;

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
        $bloomFilter = $request->query('bloom');
        $sort = $request->query('sort', 'newest');

        $questions = null;
        if ($selectedType) {
            $questions = $allQuestions->where('questionType', $selectedType);
            if ($bloomFilter) {
                $questions = $questions->where('bloomLevel', $bloomFilter);
            }

            $questions = match ($sort) {
                'oldest' => $questions->sortBy('updated_at'),
                'az' => $questions->sortBy(fn ($q) => strtolower($q->content)),
                'za' => $questions->sortByDesc(fn ($q) => strtolower($q->content)),
                'bloom' => $questions->sortBy('bloomLevel'),
                default => $questions->sortByDesc('updated_at'),
            };

            $questions = $questions->values();
        }

        return view('questions.index', [
            'course' => $course,
            'topic' => $topic,
            'counts' => $counts,
            'selectedType' => $selectedType,
            'bloomFilter' => $bloomFilter,
            'sort' => $sort,
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
            return back()->withErrors(['choices' => 'Add at least two answer choices for a Multiple Choice question.'])->withInput();
        }

        // Simple duplicate check: same content already exists under this topic
        $duplicate = Question::where('topicId', $topic->topicId)
            ->where('content', $data['content'])
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['content' => 'This exact question already exists for this topic.'])->withInput();
        }

        Question::create([
            'topicId' => $topic->topicId,
            'bloomLevel' => $data['bloomLevel'],
            'questionType' => $data['questionType'],
            'content' => $data['content'],
            'choices' => $data['questionType'] === 'Multiple Choice' ? implode(',', $choices) : null,
            'correctAnswer' => $data['correctAnswer'] ?? null,
        ]);

        return redirect()->route('courses.topics.questions.index', [$course, $topic])->with('status', 'Question added.');
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
        $view = array_filter($request->only(['type', 'bloom', 'sort']), fn ($v) => is_string($v) && $v !== '');

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