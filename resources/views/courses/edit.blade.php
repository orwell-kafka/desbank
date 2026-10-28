@extends('layouts.auth')

@section('content')
    <h1>Edit Course</h1>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('courses.update', $course) }}">
        @csrf
        @method('PUT')

        <label>Course Name</label>
        <input type="text" name="courseName" value="{{ old('courseName', $course->courseName) }}" required autofocus>

        <label>Course Code</label>
        <input type="text" name="courseCode" value="{{ old('courseCode', $course->courseCode) }}" required>

        <button type="submit">Update Course</button>
    </form>

    <div class="link">
        <a href="{{ route('courses.index') }}">&larr; Back to Courses</a>
    </div>
@endsection