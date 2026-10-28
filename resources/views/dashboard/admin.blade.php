<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - DESBank</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f5f7; margin: 0; padding: 40px; }
        .card { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #1f2937; }
        h2 { color: #374151; font-size: 18px; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        .status { color: #15803d; font-size: 13px; margin-bottom: 10px; }
        .badge { padding: 2px 8px; border-radius: 4px; font-size: 12px; }
        .badge-approved { background: #dcfce7; color: #166534; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        button { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; margin-right: 5px; }
        .approve-btn { background: #16a34a; color: #fff; }
        .reject-btn { background: #dc2626; color: #fff; }
        .logout-btn { margin-top: 20px; background: #374151; color: #fff; padding: 8px 14px; }
        .empty { color: #6b7280; font-size: 14px; font-style: italic; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Welcome, {{ auth()->user()->name }} (Admin)</h1>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        <h2>Pending Instructor Approvals</h2>
        @if ($pending->isEmpty())
            <p class="empty">No pending accounts right now.</p>
        @else
            <table>
                <tr><th>Name</th><th>Email</th><th>Action</th></tr>
                @foreach ($pending as $account)
                    <tr>
                        <td>{{ $account->name }}</td>
                        <td>{{ $account->email }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.accounts.approve', $account) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="approve-btn">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.accounts.reject', $account) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="reject-btn">Reject</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif

        <h2>All Instructor Accounts</h2>
        @if ($accounts->isEmpty())
            <p class="empty">No approved or rejected accounts yet.</p>
        @else
            <table>
                <tr><th>Name</th><th>Email</th><th>Status</th></tr>
                @foreach ($accounts as $account)
                    <tr>
                        <td>{{ $account->name }}</td>
                        <td>{{ $account->email }}</td>
                        <td><span class="badge badge-{{ $account->status }}">{{ ucfirst($account->status) }}</span></td>
                    </tr>
                @endforeach
            </table>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">Log Out</button>
        </form>
    </div>
</body>
</html>