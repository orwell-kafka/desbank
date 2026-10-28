@extends('layouts.auth')

@section('content')
    <h1>Create Instructor Account</h1>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('register.attempt') }}">
        @csrf

        <label>Full Name</label>
        <input type="text" name="name" value="{{ old('name') }}" required autofocus>

        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" required>

        <button type="submit">Register</button>
    </form>

    <div class="link">
        Already have an account? <a href="{{ route('login') }}">Log in</a>
    </div>
@endsection