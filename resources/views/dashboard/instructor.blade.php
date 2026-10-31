<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instructor Workspace · DESBank</title>
    @include('partials.head-assets')
    <link rel="stylesheet" href="{{ asset('css/workspace.css') }}">
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <header class="work-heading">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> Instructor / Overview</p>
                    <h1>Assessment workspace<span class="work-title-period">.</span></h1>
                    <p class="work-intro">Welcome back, <strong>{{ auth()->user()->name }}</strong>. Your tools for planning, assembling, and preparing assessments are here.</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="work-signout">
                    @csrf
                    <button type="submit" class="work-text-button">Log out <span aria-hidden="true">↗</span></button>
                </form>
            </header>

            <div class="work-overview-grid">
                <section class="work-feature" aria-labelledby="work-start-title">
                    <div class="work-feature-top">
                        <span class="work-feature-label">YOUR WORKSPACE</span>
                        <span class="work-feature-index">01 / 02</span>
                    </div>
                    <div class="work-feature-body">
                        <p class="work-feature-category">Course management</p>
                        <h2 id="work-start-title">Start with a course.</h2>
                        <p>Organize your subjects, manage topics and questions, then generate exams from the same workspace.</p>
                        <div class="work-feature-actions">
                            <a href="{{ route('courses.index') }}" class="work-action-primary">View my courses <span aria-hidden="true">→</span></a>
                            <a href="{{ route('courses.create') }}" class="work-action-secondary">Add a course <span aria-hidden="true">↗</span></a>
                        </div>
                    </div>
                    <div class="work-feature-bottom" aria-hidden="true">
                        <span>DESBANK / ACADEMIC TOOLS</span><span>INSTRUCTOR PORTAL</span>
                    </div>
                </section>

                <aside class="work-guide" aria-labelledby="work-guide-title">
                    <p class="work-eyebrow">THE PROCESS</p>
                    <h2 id="work-guide-title">From syllabus to exam</h2>
                    <ol class="work-steps">
                        <li><span class="work-step-num">01</span><div><strong>Courses &amp; topics</strong><p>Define the material you teach.</p></div></li>
                        <li><span class="work-step-num">02</span><div><strong>Question bank</strong><p>Organize by type and Bloom level.</p></div></li>
                        <li><span class="work-step-num">03</span><div><strong>Table of Specifications</strong><p>Plan the coverage of an assessment.</p></div></li>
                        <li><span class="work-step-num">04</span><div><strong>Generate &amp; format</strong><p>Review the exam, answer key, and print layout.</p></div></li>
                    </ol>
                </aside>
            </div>

            <section class="work-next" aria-labelledby="work-next-title">
                <div>
                    <p class="work-eyebrow">SHORTCUTS</p>
                    <h2 id="work-next-title">Continue your work</h2>
                </div>
                <div class="work-next-links">
                    <a href="{{ route('courses.index') }}"><span>Browse courses and their resources</span><span aria-hidden="true">↗</span></a>
                    <a href="{{ route('courses.create') }}"><span>Create a new course</span><span aria-hidden="true">↗</span></a>
                </div>
            </section>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
