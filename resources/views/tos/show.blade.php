<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tos->title }} · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><a href="{{ route('courses.tos.index', $course) }}">{{ $course->courseName }} ToS</a><span aria-hidden="true">/</span><span aria-current="page">{{ $tos->title }}</span>
            </nav>
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> <span class="work-course-code">{{ $course->courseCode }}</span> Table of Specifications</p>
                    <h1>{{ $tos->title }}<span class="work-title-period">.</span></h1>
                    <p class="work-intro">{{ $course->courseName }}. Read-only view of the plan used to generate exams.</p>
                </div>
            </header>

            <dl class="work-meta">
                <div><dt>Exam type</dt><dd>{{ $tos->examType }}</dd></div>
                <div><dt>Total score</dt><dd>{{ $tos->totalScore }}</dd></div>
            </dl>

            <section class="work-directory" aria-labelledby="work-t1">
                <div class="work-directory-heading">
                    <div><p class="work-eyebrow">TABLE 1</p><h2 id="work-t1">Topics &amp; contact hours</h2></div>
                </div>
                <div class="work-table-scroll" role="region" aria-label="Topics and contact hours" tabindex="0">
                    <table class="work-table work-table-plain">
                        <thead><tr>
                            <th scope="col">Topic</th>
                            <th scope="col" class="work-num">Contact hours</th>
                            <th scope="col" class="work-num">Weight (%)</th>
                            <th scope="col" class="work-num">Assigned points</th>
                        </tr></thead>
                        <tbody>
                            @php $topicPointsTotal = 0; @endphp
                            @foreach ($topicRows as $row)
                                @php $topicPointsTotal += $row->assignedPoints; @endphp
                                <tr>
                                    <td class="work-course-name">{{ $row->topicName }}</td>
                                    <td class="work-num">{{ $row->contactHours }}</td>
                                    <td class="work-num">{{ $row->generatedWeight }}%</td>
                                    <td class="work-num">{{ $row->assignedPoints }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3">Total</td>
                                <td class="work-num">{{ number_format($topicPointsTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            <section class="work-directory" style="margin-top:0;" aria-labelledby="work-t2">
                <div class="work-directory-heading">
                    <div><p class="work-eyebrow">TABLE 2</p><h2 id="work-t2">Bloom's taxonomy distribution</h2></div>
                </div>
                <div class="work-table-scroll" role="region" aria-label="Bloom distribution" tabindex="0">
                    <table class="work-table work-table-plain">
                        <thead><tr>
                            <th scope="col">Bloom level</th>
                            <th scope="col">Question type</th>
                            <th scope="col" class="work-num">Item count</th>
                            <th scope="col" class="work-num">Score</th>
                            <th scope="col" class="work-num">% of total</th>
                        </tr></thead>
                        <tbody>
                            @php $bloomScoreTotal = 0; @endphp
                            @foreach ($bloomRows as $row)
                                @php $bloomScoreTotal += $row->generatedScore; @endphp
                                <tr>
                                    <td><span class="work-chip">{{ $row->bloomLevel }}</span></td>
                                    <td>{{ $row->questionType }}</td>
                                    <td class="work-num">{{ $row->generatedItemCount }}</td>
                                    <td class="work-num">{{ $row->generatedScore }}</td>
                                    <td class="work-num">{{ $tos->totalScore > 0 ? number_format(($row->generatedScore / $tos->totalScore) * 100, 2) : 0 }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3">Total</td>
                                <td class="work-num">{{ number_format($bloomScoreTotal, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            <div class="work-below-directory"><a href="{{ route('courses.tos.index', $course) }}">← Back to ToS list</a><span>Read-only</span></div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
