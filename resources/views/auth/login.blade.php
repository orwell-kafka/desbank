@extends('layouts.auth')

@section('title', 'Log in · DESBank')

@section('content')
    <h1>Log in</h1>
    <p class="auth-lede">Use your DESBank email and password.</p>

    @if (session('status'))
        <div class="auth-alert auth-alert-status" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="auth-alert auth-alert-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf

        <div class="auth-field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   autocomplete="username" required autofocus
                   @if ($errors->has('email')) aria-invalid="true" @endif>
        </div>

        <div class="auth-field">
            <label for="password">Password</label>
            <div class="auth-password">
                <input id="password" type="password" name="password"
                       autocomplete="current-password" required
                       @if ($errors->has('password')) aria-invalid="true" @endif>
                <button type="button" class="auth-password-toggle" aria-controls="password" aria-pressed="false" aria-label="Show password" title="Show password">
                    <svg class="icon-eye" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><g class="eye-shape"><path d="M1.5 12S5.5 5 12 5s10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12z"/><circle cx="12" cy="12" r="3"/></g></svg>
                    <svg class="icon-eye-off" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false" hidden><path d="M1.5 12S5.5 5 12 5s10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12z"/><circle cx="12" cy="12" r="3"/><path d="M3 3l18 18"/></svg>
                </button>
            </div>
        </div>

        <button type="submit" class="auth-submit" data-loading="Logging in…">Log in</button>
    </form>

    <p class="auth-switch">
        New instructor? <a href="{{ route('register') }}">Create an account</a>
    </p>

    <script>
        (function () {
            var input = document.getElementById('password');
            var btn = document.querySelector('.auth-password-toggle');
            if (!input || !btn) return;
            var eye = btn.querySelector('.icon-eye');
            var eyeOff = btn.querySelector('.icon-eye-off');

            btn.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                eye.hidden = show;
                eyeOff.hidden = !show;
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                btn.title = show ? 'Hide password' : 'Show password';
                input.focus();
            });

            // The eye itself blinks "NOAMS" in Morse code (-. --- .- -- ...) on a loop: the lid opens
            // for a dot (short) or a dash (long) and stays shut in the gaps. Skipped for people who
            // prefer reduced motion.
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            var UNIT = 180; // one Morse "dot" in ms
            var morse = ['-.', '---', '.-', '--', '...']; // N O A M S
            var steps = []; // [eye open?, units]
            morse.forEach(function (letter, li) {
                letter.split('').forEach(function (sym, si) {
                    steps.push([true, sym === '.' ? 1 : 3]);
                    if (si < letter.length - 1) steps.push([false, 1]);   // gap inside a letter
                });
                if (li < morse.length - 1) steps.push([false, 3]);        // gap between letters
            });
            steps.push([false, 3]);
            steps.push([true, 20]);                                       // eye rests open, then repeat

            var i = 0;
            function tick() {
                var step = steps[i];
                btn.classList.toggle('is-closed', !step[0]);
                i = (i + 1) % steps.length;
                setTimeout(tick, step[1] * UNIT);
            }
            tick();
        })();
    </script>
@endsection
