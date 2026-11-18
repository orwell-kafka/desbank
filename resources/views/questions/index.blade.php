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
            <header class="work-heading work-heading-compact work-heading-tight">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> <span class="work-course-code">{{ $course->courseCode }}</span> Question bank</p>
                    <h1>{{ $topic->topicName }}<span class="work-title-period">.</span></h1>
                </div>
                <a href="{{ route('courses.topics.questions.create', [$course, $topic]) }}{{ $selectedType ? '?type=' . urlencode($selectedType) : '' }}" class="work-action-primary">Add a question <span aria-hidden="true">+</span></a>
            </header>

            @if (session('status'))
                <div class="work-notice" role="status">{{ session('status') }}</div>
            @endif

            @php
                $listUrl = route('courses.topics.questions.index', [$course, $topic]);
                $keep = array_filter(['q' => $search, 'bloom' => $bloomFilter, 'sort' => $sort !== 'newest' ? $sort : null], fn ($v) => $v !== null && $v !== '');
                $hasFilter = $selectedType || $bloomFilter || $search !== '';
            @endphp
            <section class="work-directory" aria-labelledby="work-questions-title">
                <nav class="work-tabs work-tabs-pills" aria-label="Question types">
                    <a href="{{ $listUrl }}{{ $keep ? '?' . http_build_query($keep) : '' }}" class="work-tab" @if (! $selectedType) aria-current="page" @endif>All <small>{{ $total }}</small></a>
                    @foreach ($counts as $type => $count)
                        <a href="{{ $listUrl }}?{{ http_build_query(array_merge(['type' => $type], $keep)) }}" class="work-tab" @if ($type === $selectedType) aria-current="page" @endif>{{ $type }} <small>{{ $count }}</small></a>
                    @endforeach
                </nav>

                <div class="work-toolbar">
                    <form method="GET" action="{{ $listUrl }}" role="search" data-filter-form="#work-results">
                        @if ($selectedType)
                            <input type="hidden" name="type" value="{{ $selectedType }}">
                        @endif
                        <div class="work-field work-field-search">
                            <label class="work-label" for="questionSearch">Search questions</label>
                            <input type="search" name="q" id="questionSearch" class="work-input" value="{{ $search }}" placeholder="Search question text or choices" autocomplete="off">
                        </div>
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
                                <option value="newest" {{ $sort == 'newest' ? 'selected' : '' }}>Date Added (Newest first)</option>
                                <option value="oldest" {{ $sort == 'oldest' ? 'selected' : '' }}>Date Added (Oldest first)</option>
                                <option value="az" {{ $sort == 'az' ? 'selected' : '' }}>Question Text (A&ndash;Z)</option>
                                <option value="za" {{ $sort == 'za' ? 'selected' : '' }}>Question Text (Z&ndash;A)</option>
                                <option value="bloom" {{ $sort == 'bloom' ? 'selected' : '' }}>Bloom's Level</option>
                            </select>
                        </div>
                        <button type="submit" class="work-row-btn">Search</button>
                        @if ($hasFilter)
                            <a href="{{ $listUrl }}" class="work-row-link work-row-link-main">Clear</a>
                        @endif
                    </form>
                    <span class="work-count" id="work-questions-title">{{ $questions->count() }} {{ $questions->count() === 1 ? 'question' : 'questions' }}</span>
                </div>

                <div class="work-results" id="work-results">
                    @if ($questions->isEmpty())
                        <div class="work-empty-inline">
                            @if ($hasFilter)
                                No questions match your search or filters.
                                <a class="work-row-link work-row-link-main" href="{{ $listUrl }}">Clear filters</a>
                            @else
                                No questions in this topic yet.
                                <a class="work-row-link work-row-link-main" href="{{ route('courses.topics.questions.create', [$course, $topic]) }}">Add one</a>
                            @endif
                        </div>
                    @else
                        <div class="work-table-scroll" role="region" aria-label="Questions" tabindex="0">
                            <table class="work-table">
                                <thead><tr>
                                    <th scope="col">No.</th>
                                    <th scope="col">Question</th>
                                    @unless ($selectedType)
                                        <th scope="col">Type</th>
                                    @endunless
                                    <th scope="col">Bloom level</th>
                                    <th scope="col" aria-label="Actions"></th>
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
                                            @unless ($selectedType)
                                                <td><span class="work-chip work-chip-gold">{{ $question->questionType }}</span></td>
                                            @endunless
                                            <td><span class="work-chip">{{ $question->bloomLevel }}</span></td>
                                            <td>
                                                <div class="work-menu" data-menu>
                                                    <button type="button" class="work-menu-btn" aria-haspopup="menu" aria-expanded="false" aria-controls="question-menu-{{ $question->questionId }}" aria-label="More actions for question {{ $loop->iteration }}">
                                                        <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false"><circle cx="8" cy="3" r="1.5"/><circle cx="8" cy="8" r="1.5"/><circle cx="8" cy="13" r="1.5"/></svg>
                                                    </button>
                                                    <div class="work-menu-list" id="question-menu-{{ $question->questionId }}" role="menu" hidden>
                                                        <a href="{{ route('courses.topics.questions.edit', [$course, $topic, $question]) }}" role="menuitem" class="work-menu-item">Edit</a>
                                                        <form method="POST" action="{{ route('courses.topics.questions.destroy', [$course, $topic, $question]) }}" role="none" data-confirm="Delete this question?" data-confirm-item="{{ \Illuminate\Support\Str::limit($question->content, 90) }}" data-confirm-label="Delete question">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="type" value="{{ $selectedType }}">
                                                            <input type="hidden" name="bloom" value="{{ $bloomFilter }}">
                                                            <input type="hidden" name="sort" value="{{ $sort }}">
                                                            <input type="hidden" name="q" value="{{ $search }}">
                                                            <button type="submit" role="menuitem" class="work-menu-item work-menu-item-danger">Delete</button>
                                                        </form>
                                                    </div>
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

            <div class="work-below-directory"><a data-back href="{{ route('courses.topics.index', $course) }}">← Back to topics</a><span>Question bank</span></div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
