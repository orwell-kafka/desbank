<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'DESBank')</title>
    @include('partials.head-assets')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body class="auth-page">
    <div class="auth-layout">
        <header class="auth-brand">
            <div class="auth-brand-head">
                <div class="auth-mark" aria-hidden="true"></div>
                <div>
                    <div class="auth-name">DESBank</div>
                    <p class="auth-sub">Question bank and exam builder</p>
                </div>
            </div>

            <ol class="auth-flow" aria-label="How DESBank works">
                <li><strong>Course</strong>Set up the subject you teach.</li>
                <li><strong>Topic</strong>Group your questions by topic.</li>
                <li><strong>Question bank</strong>Add questions by type and Bloom level.</li>
                <li><strong>Table of Specifications</strong>Plan how many items each topic and level gets.</li>
                <li><strong>Exam</strong>Generate, format and print it, with an answer key.</li>
            </ol>
        </header>

        <main class="auth-main">
            <div class="auth-panel">
                @yield('content')
            </div>
        </main>
    </div>
    <script>
        // Show progress after submit and block double submits. Server behavior is unchanged.
        document.querySelectorAll('.auth-panel form').forEach(function (form) {
            var btn = form.querySelector('.auth-submit');
            if (!btn) return;
            var label = btn.textContent;
            form.addEventListener('submit', function (e) {
                if (form.dataset.busy) { e.preventDefault(); return; }
                form.dataset.busy = '1';
                btn.classList.add('is-loading');
                btn.textContent = btn.dataset.loading || 'Working…';
                btn.setAttribute('aria-busy', 'true');
                setTimeout(function () { btn.disabled = true; }, 0);
            });
            // Back button restores the page from cache: reset the button.
            window.addEventListener('pageshow', function (e) {
                if (!e.persisted) return;
                delete form.dataset.busy;
                btn.disabled = false;
                btn.classList.remove('is-loading');
                btn.removeAttribute('aria-busy');
                btn.textContent = label;
            });
        });
    </script>
</body>
</html>
