@extends('layouts.auth')

@section('content')
    <h1>Edit Topic</h1>
    <p style="color:#6b7280; font-size:14px; margin-top:-10px;">{{ $course->courseName }}</p>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('courses.topics.update', [$course, $topic]) }}">
        @csrf
        @method('PUT')

        <label>Topic Name</label>
        <input type="text" name="topicName" value="{{ old('topicName', $topic->topicName) }}" required autofocus>

        <button type="submit">Update Topic</button>
    </form>

    <div class="link">
        <a href="{{ route('courses.topics.index', $course) }}">&larr; Back to Topics</a>
    </div>
@endsection