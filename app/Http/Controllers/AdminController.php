<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $pending = Account::where('role', 'Instructor')
            ->where('status', 'pending')
            ->orderBy('name')
            ->get();

        $accounts = Account::where('role', 'Instructor')
            ->where('status', '!=', 'pending')
            ->orderBy('name')
            ->get();

        return view('dashboard.admin', compact('pending', 'accounts'));
    }

    public function approve(Account $account)
    {
        abort_if($account->role !== 'Instructor', 404);
        $account->forceFill(['status' => 'approved'])->save();
        return back()->with('status', "{$account->name}'s account approved.");
    }

    public function reject(Account $account)
    {
        abort_if($account->role !== 'Instructor', 404);
        $account->forceFill(['status' => 'rejected'])->save();
        return back()->with('status', "{$account->name}'s account rejected.");
    }
}