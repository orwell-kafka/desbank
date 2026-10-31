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
            <input id="password" type="password" name="password"
                   autocomplete="current-password" required
                   @if ($errors->has('password')) aria-invalid="true" @endif>
        </div>

        <button type="submit" class="auth-submit" data-loading="Logging in…">Log in</button>
    </form>

    <p class="auth-switch">
        New instructor? <a href="{{ route('register') }}">Create an account</a>
    </p>
@endsection
