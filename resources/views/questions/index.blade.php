<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Questions - {{ $topic->topicName }} · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><a href="{{ route('courses.topics.index', $course) }}">{{ $course->courseName }}</a><span aria-hidden="true">/</span>
                @if ($selectedType)
                    <a href="{{ route('courses.topics.questions.index', [$course, $topic]) }}">{{ $topic->topicName }}</a><span aria-hidden="true">/</span><span aria-current="page">{{ $selectedType }}</span>
                @else
                    <span aria-current="page">{{ $topic->topicName }}</span>
                @endif
            </nav>
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> <span class="work-course-code">{{ $course->courseCode }}</span> Question bank</p>
                    <h1>{{ $topic->topicName }}<span class="work-title-period">.</span></h1>
                    <p class="work-intro">
                        @if ($selectedType)
                            Reviewing <strong>{{ $selectedType }}</strong> questions. Filter by Bloom level, change the order, or edit an item.
                        @else
                            Choose a question type to review its items, or add a new question to this topic.
                        @endif
                    </p>
                </div>
                <a href="{{ route('courses.topics.questions.create', [$course, $topic]) }}" class="work-action-primary">Add a question <span aria-hidden="true">+</span></a>
            </header>

            @if (session('status'))
                <div class="work-notice" role="status">{{ session('status') }}</div>
            @endif

            @if (! $selectedType)
                <section class="work-directory" aria-labelledby="work-types-title">
                    <div class="work-directory-heading">
                        <div>
                            <p class="work-eyebrow">BY QUESTION TYPE</p>
                            <h2 id="work-types-title">Question types</h2>
                        </div>
                        <span class="work-count">{{ $counts->sum() }} {{ $counts->sum() === 1 ? 'question' : 'questions' }}</span>
                    </div>
                    <ul class="work-types">
                        @foreach ($counts as $type => $count)
                            <li>
                                <a href="{{ route('courses.topics.questions.index', [$course, $topic]) }}?type={{ urlencode($type) }}" class="work-type {{ $count == 0 ? 'is-empty' : '' }}">
                                    <span class="work-type-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="work-type-name">{{ $type }}</span>
                                    <span class="work-type-count"><b>{{ $count }}</b><span>question{{ $count == 1 ? '' : 's' }}</span></span>
                                    <span class="work-type-arrow" aria-hidden="true">→</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @else
                <section class="work-directory" aria-labelledby="work-questions-title">
                    <nav class="work-tabs" aria-label="Question types">
                        @foreach ($counts as $type => $count)
                            <a href="{{ route('courses.topics.questions.index', [$course, $topic]) }}?type={{ urlencode($type) }}" class="work-tab" @if ($type === $selectedType) aria-current="page" @endif>{{ $type }} <small>{{ $count }}</small></a>
                        @endforeach
                    </nav>

                    <div class="work-toolbar">
                        <form method="GET" action="{{ route('courses.topics.questions.index', [$course, $topic]) }}" data-filter-form="#work-results">
                            <input type="hidden" name="type" value="{{ $selectedType }}">
                            <div class="work-field">
                                <label class="work-label" for="bloomFilter">Bloom's level</label>
                                <select name="bloom" id="bloomFilter" class="work-select" onchange="this.form.submit()">
                                    <option value="">All Levels</option>
                                    @foreach ($bloomLevels as $level)
                                        <option value="{{ $level }}" {{ $bloomFilter == $level ? 'selected' : '' }}>{{ $level }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="work-field">
                                <label class="work-label" for="sortOrder">Sort by</label>
                                <select name="sort" id="sortOrder" class="work-select" onchange="this.form.submit()">
                                    <option value="newest" {{ $sort == 'newest' ? 'selected' : '' }}>Date Modified (Newest)</option>
                                    <option value="oldest" {{ $sort == 'oldest' ? 'selected' : '' }}>Date Modified (Oldest)</option>
                                    <option value="az" {{ $sort == 'az' ? 'selected' : '' }}>Question Text (A&ndash;Z)</option>
                                    <option value="za" {{ $sort == 'za' ? 'selected' : '' }}>Question Text (Z&ndash;A)</option>
                                    <option value="bloom" {{ $sort == 'bloom' ? 'selected' : '' }}>Bloom's Level</option>
                                </select>
                            </div>
                            <noscript><button type="submit" class="work-row-btn">Apply filters</button></noscript>
                        </form>
                        <span class="work-count" id="work-questions-title">{{ $questions->count() }} {{ $questions->count() === 1 ? 'question' : 'questions' }}</span>
                    </div>

                    <div class="work-results" id="work-results">
                        @if ($questions->isEmpty())
                            <div class="work-empty-inline">
                                No {{ $selectedType }} questions match this filter.
                                @if ($bloomFilter)
                                    <a class="work-row-link work-row-link-main" href="{{ route('courses.topics.questions.index', [$course, $topic]) }}?type={{ urlencode($selectedType) }}">Clear the Bloom filter</a>
                                @else
                                    <a class="work-row-link work-row-link-main" href="{{ route('courses.topics.questions.create', [$course, $topic]) }}">Add one</a>
                                @endif
                            </div>
                        @else
                            <div class="work-table-scroll" role="region" aria-label="{{ $selectedType }} questions" tabindex="0">
                                <table class="work-table">
                                    <thead><tr>
                                        <th scope="col">No.</th>
                                        <th scope="col">Question</th>
                                        <th scope="col">Bloom level</th>
                                        <th scope="col">Manage</th>
                                    </tr></thead>
                                    <tbody>
                                        @foreach ($questions as $question)
                                            <tr>
                                                <td class="work-table-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                                <td>
                                                    <p class="work-q-text">{{ $question->content }}</p>
                                                    @if ($question->choices)
                                                        @php $letters = ['A','B','C','D','E','F','G','H']; @endphp
                                                        <ul class="work-choices" aria-label="Answer choices">
                                                            @foreach (explode(',', $question->choices) as $i => $choice)
                                                                <li><b>{{ $letters[$i] ?? '' }}.</b><span>{{ trim($choice) }}</span></li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </td>
                                                <td><span class="work-chip">{{ $question->bloomLevel }}</span></td>
                                                <td>
                                                    <div class="work-row-actions">
                                                        <a href="{{ route('courses.topics.questions.edit', [$course, $topic, $question]) }}" class="work-row-link work-row-link-main">Edit</a>
                                                        <form method="POST" action="{{ route('courses.topics.questions.destroy', [$course, $topic, $question]) }}" onsubmit="return confirm('Delete this question?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="type" value="{{ $selectedType }}">
                                                            <input type="hidden" name="bloom" value="{{ $bloomFilter }}">
                                                            <input type="hidden" name="sort" value="{{ $sort }}">
                                                            <button type="submit" class="work-row-link work-row-delete">Delete</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            <div class="work-below-directory"><a href="{{ route('courses.topics.index', $course) }}">← Back to topics</a><span>Question bank</span></div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
