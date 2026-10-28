<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Questions - {{ $topic->topicName }} - DESBank</title>
    @include('partials.head-assets')
    <style>
        .filter-bar { display: flex; gap: 12px; align-items: center; background: var(--canvas); border: 1px solid var(--border); padding: 10px 14px; border-radius: var(--radius-sm); margin-bottom: 15px; }
        .filter-bar label { font-size: 13px; color: var(--text-muted); margin: 0; }
    </style>
</head>
<body>
    <div class="db-shell">
        @include('partials.navbar')
        <div class="db-card">
            <h1>Question Bank</h1>
            <p class="db-subtitle">{{ $course->courseName }} &rarr; {{ $topic->topicName }}</p>

            @if (session('status'))
                <div class="db-alert db-alert-success">{{ session('status') }}</div>
            @endif

            @if (! $selectedType)
                <a href="{{ route('courses.topics.questions.create', [$course, $topic]) }}" class="db-btn db-btn-primary" style="margin-bottom:15px; display:inline-block;">+ Add Question</a>

                <div class="db-tile-grid">
                    @foreach ($counts as $type => $count)
                        <a href="{{ route('courses.topics.questions.index', [$course, $topic]) }}?type={{ urlencode($type) }}" class="db-tile">
                            <div class="db-tile-name">{{ $type }}</div>
                            <div class="db-tile-count">{{ $count }}</div>
                            <div class="db-tile-label">question{{ $count == 1 ? '' : 's' }}</div>
                        </a>
                    @endforeach
                </div>
            @else
                <a href="{{ route('courses.topics.questions.index', [$course, $topic]) }}" class="db-link" style="display:inline-block; margin-bottom:10px;">&larr; Back to all types</a>
                <a href="{{ route('courses.topics.questions.create', [$course, $topic]) }}" class="db-btn db-btn-primary" style="margin-bottom:15px; display:inline-block;">+ Add Question</a>

                <div class="filter-bar">
                    <form method="GET" action="{{ route('courses.topics.questions.index', [$course, $topic]) }}" style="display:flex; gap:12px; align-items:center;">
                        <input type="hidden" name="type" value="{{ $selectedType }}">
                        <label>Bloom's Level:</label>
                        <select name="bloom" class="db-select" style="width:auto;" onchange="this.form.submit()">
                            <option value="">All Levels</option>
                            @foreach ($bloomLevels as $level)
                                <option value="{{ $level }}" {{ $bloomFilter == $level ? 'selected' : '' }}>{{ $level }}</option>
                            @endforeach
                        </select>

                        <label>Sort by:</label>
                        <select name="sort" class="db-select" style="width:auto;" onchange="this.form.submit()">
                            <option value="newest" {{ $sort == 'newest' ? 'selected' : '' }}>Date Modified (Newest)</option>
                            <option value="oldest" {{ $sort == 'oldest' ? 'selected' : '' }}>Date Modified (Oldest)</option>
                            <option value="az" {{ $sort == 'az' ? 'selected' : '' }}>Question Text (A&ndash;Z)</option>
                            <option value="za" {{ $sort == 'za' ? 'selected' : '' }}>Question Text (Z&ndash;A)</option>
                            <option value="bloom" {{ $sort == 'bloom' ? 'selected' : '' }}>Bloom's Level</option>
                        </select>
                    </form>
                </div>

                <h2 style="margin-top:0; font-size:16px;">{{ $selectedType }} ({{ $questions->count() }})</h2>

                @if ($questions->isEmpty())
                    <p style="color:var(--text-muted); font-style:italic;">No questions match this filter.</p>
                @else
                    <table class="db-table">
                        <tr><th>Question</th><th>Bloom Level</th><th>Actions</th></tr>
                        @foreach ($questions as $question)
                            <tr>
                                <td>
                                    {{ $question->content }}
                                    @if ($question->choices)
                                        <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">
                                            @php $letters = ['A','B','C','D','E','F','G','H']; @endphp
                                            @foreach (explode(',', $question->choices) as $i => $choice)
                                                <span style="margin-right:10px;">{{ $letters[$i] ?? '' }}. {{ trim($choice) }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td><span class="db-chip">{{ $question->bloomLevel }}</span></td>
                                <td>
                                    <a href="{{ route('courses.topics.questions.edit', [$course, $topic, $question]) }}" class="db-btn db-btn-secondary">Edit</a>
                                    <form method="POST" action="{{ route('courses.topics.questions.destroy', [$course, $topic, $question]) }}" style="display:inline" onsubmit="return confirm('Delete this question?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="db-btn db-btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            @endif

            <a href="{{ route('courses.topics.index', $course) }}" class="db-link db-back-link">&larr; Back to Topics</a>
        </div>
    </div>
</body>
</html>