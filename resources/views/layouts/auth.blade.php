<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DESBank</title>
    @include('partials.head-assets')
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-wrap { width: 100%; max-width: 380px; padding: 20px; }
        .auth-brand { text-align: center; margin-bottom: 20px; }
        .auth-brand .mark { display: inline-block; width: 40px; height: 40px; background: var(--navy); border-radius: 8px; border-top: 3px solid var(--amber); margin-bottom: 10px; }
        .auth-brand .name { font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--navy); }
        .auth-brand .tagline { font-family: var(--font-mono); font-size: 11px; color: var(--text-muted); letter-spacing: 0.03em; margin-top: 2px; }
        .auth-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 32px; }
        .auth-card h1 { font-size: 19px; margin: 0 0 18px 0; }
        button[type=submit] { margin-top: 20px; width: 100%; padding: 11px; background: var(--amber); color: var(--navy-dark); border: none; border-radius: 5px; cursor: pointer; font-family: var(--font-body); font-weight: 700; font-size: 14px; }
        button[type=submit]:hover { background: var(--amber-dark); }
        .error { color: #A13333; background: var(--danger-tint); font-size: 13px; margin-top: 12px; padding: 10px; border-radius: 5px; }
        .status { color: #146B4C; background: var(--success-tint); font-size: 13px; margin-top: 12px; padding: 10px; border-radius: 5px; }
        .link { margin-top: 18px; font-size: 13px; text-align: center; color: var(--text-muted); }
        .link a { color: var(--navy); font-weight: 600; text-decoration: none; }
        label { display: block; margin-top: 14px; font-size: 13px; font-weight: 600; color: var(--navy); }
        input { width: 100%; padding: 9px 12px; margin-top: 5px; border: 1px solid var(--border); border-radius: 5px; box-sizing: border-box; font-family: var(--font-body); font-size: 14px; }
        input:focus { outline: 2px solid var(--amber); outline-offset: 1px; border-color: var(--amber); }
    </style>
</head>
<body>
    <div class="auth-wrap">
        <div class="auth-brand">
            <div class="mark"></div>
            <div class="name">DESBank</div>
            <div class="tagline">TEST BANK &amp; EXAM GENERATION</div>
        </div>
        <div class="auth-card">
            @yield('content')
        </div>
    </div>
</body>
</html>