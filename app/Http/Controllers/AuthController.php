<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Slow down password guessing: 5 wrong tries per email+IP and 20 per IP, each per minute.
        $emailKey = 'login:' . Str::lower($credentials['email']) . '|' . $request->ip();
        $ipKey = 'login-ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($emailKey, 5) || RateLimiter::tooManyAttempts($ipKey, 20)) {
            $seconds = max(RateLimiter::availableIn($emailKey), RateLimiter::availableIn($ipKey));
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $account = Account::where('email', $credentials['email'])->first();

        if (! $account || ! Hash::check($credentials['password'], $account->password)) {
            RateLimiter::hit($emailKey, 60);
            RateLimiter::hit($ipKey, 60);
            throw ValidationException::withMessages([
                'email' => 'Those credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($emailKey);

        if ($account->role === 'Instructor' && $account->status !== 'approved') {
            throw ValidationException::withMessages([
                'email' => 'Your account is still pending Admin approval.',
            ]);
        }

        Auth::login($account);
        $request->session()->regenerate();

        return $account->isAdmin()
            ? redirect()->route('dashboard.admin')
            : redirect()->route('courses.index');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:accounts,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        Account::forceCreate([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'Instructor',
            'status' => 'pending',
        ]);

        return redirect()->route('login')
            ->with('status', 'Account created. Please wait for Admin approval before logging in.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}