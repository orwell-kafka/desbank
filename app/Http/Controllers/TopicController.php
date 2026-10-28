<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index(Course $course)
    {
        $this->authorizeOwner($course);
        $topics = $course->topics()->orderBy('topicName')->get();

        return view('topics.index', compact('course', 'topics'));
    }

    public function create(Course $course)
    {
        $this->authorizeOwner($course);
        return view('topics.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorizeOwner($course);

        $data = $request->validate([
            'topicName' => ['required', 'string', 'max:150'],
        ]);

        Topic::create([
            'courseId' => $course->courseId,
            'topicName' => $data['topicName'],
        ]);

        return redirect()->route('courses.topics.index', $course)->with('status', 'Topic added.');
    }

    public function edit(Course $course, Topic $topic)
    {
        $this->authorizeOwner($course);
        $this->authorizeBelongsToCourse($course, $topic);

        return view('topics.edit', compact('course', 'topic'));
    }

    public function update(Request $request, Course $course, Topic $topic)
    {
        $this->authorizeOwner($course);
        $this->authorizeBelongsToCourse($course, $topic);

        $data = $request->validate([
            'topicName' => ['required', 'string', 'max:150'],
        ]);

        $topic->update($data);

        return redirect()->route('courses.topics.index', $course)->with('status', 'Topic updated.');
    }

    public function destroy(Course $course, Topic $topic)
    {
        $this->authorizeOwner($course);
        $this->authorizeBelongsToCourse($course, $topic);

        $topic->delete();

        return redirect()->route('courses.topics.index', $course)->with('status', 'Topic deleted.');
    }

    private function authorizeOwner(Course $course): void
    {
        if ($course->accountId !== auth()->id()) {
            abort(403, 'You do not own this course.');
        }
    }

    private function authorizeBelongsToCourse(Course $course, Topic $topic): void
    {
        if ($topic->courseId !== $course->courseId) {
            abort(404);
        }
    }
}