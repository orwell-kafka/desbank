<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::where('accountId', auth()->id())
            ->orderBy('courseName')
            ->get();

        return view('courses.index', compact('courses'));
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
        ]);

        Course::create([
            'accountId' => auth()->id(),
            'courseName' => $data['courseName'],
            'courseCode' => $data['courseCode'],
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
        ]);

        $course->update($data);

        return redirect()->route('courses.index')->with('status', 'Course updated.');
    }

    public function destroy(Course $course)
    {
        $this->authorizeOwner($course);
        $course->delete();

        return redirect()->route('courses.index')->with('status', 'Course deleted.');
    }

    private function authorizeOwner(Course $course): void
    {
        if ($course->accountId !== auth()->id()) {
            abort(403, 'You do not own this course.');
        }
    }
}