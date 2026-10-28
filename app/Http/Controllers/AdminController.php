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
        $account->update(['status' => 'approved']);
        return back()->with('status', "{$account->name}'s account approved.");
    }

    public function reject(Account $account)
    {
        $account->update(['status' => 'rejected']);
        return back()->with('status', "{$account->name}'s account rejected.");
    }
}