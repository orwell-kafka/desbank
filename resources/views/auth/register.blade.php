@extends('layouts.auth')

@section('title', 'Create account · DESBank')

@section('content')
    <h1>Create an instructor account</h1>
    <p class="auth-lede">An administrator approves new accounts before you can log in.</p>

    @if ($errors->any())
        <div class="auth-alert auth-alert-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('register.attempt') }}">
        @csrf

        <div class="auth-field">
            <label for="name">Full name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   autocomplete="name" required autofocus
                   @if ($errors->has('name')) aria-invalid="true" @endif>
        </div>

        <div class="auth-field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   autocomplete="email" required
                   @if ($errors->has('email')) aria-invalid="true" @endif>
        </div>

        <div class="auth-field">
            <label for="password">Password</label>
            <input id="password" type="password" name="password"
                   autocomplete="new-password" minlength="8" required aria-describedby="password-hint"
                   @if ($errors->has('password')) aria-invalid="true" @endif>
            <p class="auth-hint" id="password-hint">At least 8 characters.</p>
        </div>

        <div class="auth-field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   autocomplete="new-password" required>
        </div>

        <button type="submit" class="auth-submit" data-loading="Creating account…">Create account</button>
    </form>

    <p class="auth-switch">
        Already have an account? <a href="{{ route('login') }}">Log in</a>
    </p>
@endsection
