@extends('layouts.auth')

@section('content')
    <h1>DESBank Login</h1>

    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf

        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Log In</button>
    </form>

    <div class="link">
        Instructor? <a href="{{ route('register') }}">Create an account</a>
    </div>
@endsection