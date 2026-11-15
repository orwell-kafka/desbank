<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Tos;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    /** JPG, PNG or WebP up to 2 MB. SVG is left out on purpose: it can carry scripts. */
    private const COVER_RULES = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];

    public function index()
    {
        $courses = Course::where('accountId', auth()->id())
            ->orderBy('courseName')
            ->get();

        return view('courses.index', compact('courses'));
    }

    /** The course's own page: every option for the course lives here, not on the course list. */
    public function show(Course $course)
    {
        $this->authorizeOwner($course);

        $topics = $course->topics()->orderBy('topicName')->get();
        $stats = [
            'topics' => $topics->count(),
            'questions' => DB::table('question')->join('topic', 'topic.topicId', '=', 'question.topicId')->where('topic.courseId', $course->courseId)->count(),
            'tos' => $course->tos()->count(),
            'exams' => DB::table('exam')->join('tos', 'tos.tosId', '=', 'exam.tosId')->where('tos.courseId', $course->courseId)->count()
                + DB::table('exam')->whereNull('tosId')->where('courseId', $course->courseId)->count(),
        ];

        return view('courses.show', compact('course', 'topics', 'stats'));
    }

    public function create()
    {
        return view('courses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'courseName' => ['required', 'string', 'max:150'],
            'courseCode' => ['required', 'string', 'max:20', 'unique:courses,courseCode'],
            'creditUnits' => ['required', 'numeric', 'min:0.5', 'max:99'],
            'coverImage' => self::COVER_RULES,
        ]);

        Course::create([
            'accountId' => auth()->id(),
            'courseName' => $data['courseName'],
            'courseCode' => $data['courseCode'],
            'creditUnits' => $data['creditUnits'],
            'coverImage' => $request->hasFile('coverImage') ? $this->storeCover($request->file('coverImage')) : null,
        ]);

        return redirect()->route('courses.index')->with('status', 'Course created.');
    }

    public function edit(Course $course)
    {
        $this->authorizeOwner($course);
        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorizeOwner($course);

        $data = $request->validate([
            'courseName' => ['required', 'string', 'max:150'],
            'courseCode' => ['required', 'string', 'max:20', 'unique:courses,courseCode,' . $course->courseId . ',courseId'],
            'creditUnits' => ['required', 'numeric', 'min:0.5', 'max:99'],
            'coverImage' => self::COVER_RULES,
            'removeCover' => ['nullable', 'boolean'],
        ]);

        $fields = [
            'courseName' => $data['courseName'],
            'courseCode' => $data['courseCode'],
            'creditUnits' => $data['creditUnits'],
        ];
        if ($request->hasFile('coverImage')) {
            $this->deleteCover($course);
            $fields['coverImage'] = $this->storeCover($request->file('coverImage'));
        } elseif ($request->boolean('removeCover')) {
            $this->deleteCover($course);
            $fields['coverImage'] = null;
        }

        $course->update($fields);

        // Every ToS title is "CODE: Name", so keep them in step with the course.
        foreach ($course->tos()->get() as $tos) {
            DB::table('tos')->where('tosId', $tos->tosId)->update(['title' => Tos::headingTitle($course)]);
        }

        return redirect()->route('courses.index')->with('status', 'Course updated.');
    }

    public function destroy(Course $course)
    {
        $this->authorizeOwner($course);
        $this->deleteCover($course);
        $course->delete();

        return redirect()->route('courses.index')->with('status', 'Course deleted.');
    }

    private function authorizeOwner(Course $course): void
    {
        if ($course->accountId !== auth()->id()) {
            abort(403, 'You do not own this course.');
        }
    }

    /** Saves the picture under a random name and returns that name. */
    private function storeCover(UploadedFile $file): string
    {
        $dir = public_path(Course::COVER_DIR);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $name = Str::random(40) . '.' . $file->extension();
        $file->move($dir, $name);

        return $name;
    }

    private function deleteCover(Course $course): void
    {
        if ($course->coverImage) {
            $path = public_path(Course::COVER_DIR . '/' . basename($course->coverImage));
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
