<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        <div class="work-adminbar"><span class="work-adminbar-brand">DESBank</span><span class="work-adminbar-role">Administration</span></div>

        <main id="main-content" class="work-main">
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> Admin / Accounts</p>
                    <h1>Account approvals<span class="work-title-period">.</span></h1>
                    <p class="work-intro">Welcome, <strong>{{ auth()->user()->name }}</strong>. Review instructor sign-ups and see the status of every instructor account.</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="work-signout">
                    @csrf
                    <button type="submit" class="work-text-button">Log out <span aria-hidden="true">↗</span></button>
                </form>
            </header>

            @if (session('status'))
                <div class="work-notice" role="status">{{ session('status') }}</div>
            @endif

            <section class="work-directory" aria-labelledby="work-pending">
                <div class="work-directory-heading">
                    <div><p class="work-eyebrow">AWAITING DECISION</p><h2 id="work-pending">Pending instructor approvals</h2></div>
                    <span class="work-count">{{ $pending->count() }} pending</span>
                </div>
                @if ($pending->isEmpty())
                    <div class="work-empty-inline">No pending accounts right now.</div>
                @else
                    <div class="work-table-scroll" role="region" aria-label="Pending accounts" tabindex="0">
                        <table class="work-table work-table-narrow">
                            <thead><tr>
                                <th scope="col">No.</th>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Action</th>
                            </tr></thead>
                            <tbody>
                                @foreach ($pending as $account)
                                    <tr>
                                        <td class="work-table-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                        <td class="work-person">{{ $account->name }}</td>
                                        <td class="work-email">{{ $account->email }}</td>
                                        <td>
                                            <div class="work-row-actions">
                                                <form method="POST" action="{{ route('admin.accounts.approve', $account) }}" data-pending>
                                                    @csrf
                                                    <button type="submit" class="work-row-btn work-row-btn-ok" data-pending-label="Approving…">Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.accounts.reject', $account) }}" onsubmit="return confirm('Reject this account?');" data-pending>
                                                    @csrf
                                                    <button type="submit" class="work-row-btn work-row-btn-bad" data-pending-label="Rejecting…">Reject</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="work-directory" style="margin-top:0;" aria-labelledby="work-accounts">
                <div class="work-directory-heading">
                    <div><p class="work-eyebrow">DECIDED</p><h2 id="work-accounts">All instructor accounts</h2></div>
                    <span class="work-count">{{ $accounts->count() }} {{ $accounts->count() === 1 ? 'account' : 'accounts' }}</span>
                </div>
                @if ($accounts->isEmpty())
                    <div class="work-empty-inline">No approved or rejected accounts yet.</div>
                @else
                    <div class="work-table-scroll" role="region" aria-label="Instructor accounts" tabindex="0">
                        <table class="work-table work-table-narrow">
                            <thead><tr>
                                <th scope="col">No.</th>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Status</th>
                            </tr></thead>
                            <tbody>
                                @foreach ($accounts as $account)
                                    @php $statusChip = ['approved' => 'work-chip-ok', 'rejected' => 'work-chip-bad'][$account->status] ?? 'work-chip-gold'; @endphp
                                    <tr>
                                        <td class="work-table-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                        <td class="work-person">{{ $account->name }}</td>
                                        <td class="work-email">{{ $account->email }}</td>
                                        <td><span class="work-chip {{ $statusChip }}">{{ ucfirst($account->status) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
