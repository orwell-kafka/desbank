@php
    // Which entry is the current page. Topics and Questions are separate entries, so they are matched exactly.
    $navCourses   = request()->routeIs('courses.index', 'courses.create', 'courses.edit', 'courses.show');
    $navTopics    = request()->routeIs('courses.topics.*');
    $navTos       = request()->routeIs('courses.tos.*');
    $navExams     = request()->routeIs('courses.exams.*', 'courses.quizzes.*');
@endphp
<nav class="db-sidenav" id="dbSidenav" aria-label="Main navigation">
    <div class="db-sidenav-top">
        <button type="button" class="db-sidenav-toggle" id="dbSidenavToggle" aria-expanded="false" aria-controls="dbSidenavList" aria-label="Expand navigation">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
        <a href="{{ route('dashboard.instructor') }}" class="db-sidenav-brand">DESBank</a>
    </div>

    <ul class="db-sidenav-list" id="dbSidenavList">
        <li>
            <a href="{{ route('dashboard.instructor') }}" class="db-sidenav-link" @if (request()->routeIs('dashboard.instructor')) aria-current="page" @endif>
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 11.5 12 5l8 6.5V19a1 1 0 0 1-1 1h-4v-5h-6v5H5a1 1 0 0 1-1-1z"/></svg>
                <span class="db-sidenav-label">Workspace</span>
            </a>
        </li>
        <li>
            <a href="{{ route('courses.index') }}" class="db-sidenav-link" @if ($navCourses) aria-current="page" @endif>
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 5.5A1.5 1.5 0 0 1 6.5 4H19v14H6.5A1.5 1.5 0 0 0 5 19.5zM5 19.5A1.5 1.5 0 0 0 6.5 21H19M9 8h6"/></svg>
                <span class="db-sidenav-label">My Courses</span>
            </a>
        </li>

        @isset($course)
            <li class="db-sidenav-context" title="{{ $course->courseName }}">{{ $course->courseName }}</li>
            <li>
                <a href="{{ route('courses.topics.index', $course) }}" class="db-sidenav-link" @if ($navTopics) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 6h11M9 12h11M9 18h11M4.5 6h.01M4.5 12h.01M4.5 18h.01"/></svg>
                    <span class="db-sidenav-label">Topics</span>
                </a>
            </li>
            <li>
                <a href="{{ route('courses.tos.index', $course) }}" class="db-sidenav-link" @if ($navTos) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="4" y="4" width="16" height="16" rx="1.5"/><path d="M4 10h16M10 10v10"/></svg>
                    <span class="db-sidenav-label">ToS</span>
                </a>
            </li>
            <li>
                <a href="{{ route('courses.exams.index', $course) }}" class="db-sidenav-link" @if ($navExams) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M7 3.5h7l4 4V20.5H7zM14 3.5v4h4M9.5 12h5M9.5 15.5h5"/></svg>
                    <span class="db-sidenav-label">Exams</span>
                </a>
            </li>
        @endisset
    </ul>

    <form method="POST" action="{{ route('logout') }}" class="db-sidenav-foot">
        @csrf
        <button type="submit" class="db-sidenav-link">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M14 4h4a1.5 1.5 0 0 1 1.5 1.5v13A1.5 1.5 0 0 1 18 20h-4M10 8l-4 4 4 4M6 12h10"/></svg>
            <span class="db-sidenav-label">Log out</span>
        </button>
    </form>
</nav>
<script>
    (function () {
        var nav = document.getElementById('dbSidenav');
        var toggle = document.getElementById('dbSidenavToggle');
        if (!nav || !toggle) return;
        // Hover and keyboard focus expand the bar by themselves; the button keeps it open for touch screens.
        function setOpen(open) {
            nav.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Collapse navigation' : 'Expand navigation');
        }
        toggle.addEventListener('click', function () { setOpen(!nav.classList.contains('is-open')); });
        document.addEventListener('click', function (e) {
            if (nav.classList.contains('is-open') && !nav.contains(e.target)) setOpen(false);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && nav.classList.contains('is-open')) { setOpen(false); toggle.focus(); }
        });
    })();
</script>
