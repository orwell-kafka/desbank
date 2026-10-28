<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instructor Dashboard - DESBank</title>
    @include('partials.head-assets')
</head>
<body>
    <div class="db-shell">
        @include('partials.navbar')
        <div class="db-card">
            <h1>Welcome, {{ auth()->user()->name }}</h1>
            <p class="db-subtitle">Instructor Dashboard</p>

            <p style="margin-top:20px;">
                <a href="{{ route('courses.index') }}" class="db-btn db-btn-primary">Manage My Courses &rarr;</a>
            </p>

            <form method="POST" action="{{ route('logout') }}" style="margin-top:20px;">
                @csrf
                <button type="submit" class="db-btn db-btn-secondary">Log Out</button>
            </form>
        </div>
    </div>
</body>
</html>