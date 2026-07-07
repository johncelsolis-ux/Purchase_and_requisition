@extends('layouts.app')

@section('title', 'Approvals')

@section('content')
<h1 class="text-xl font-bold mb-4">PR &amp; PO Approval Queue</h1>

<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-blue-50 rounded-lg p-4">
        <div class="text-xs text-gray-500 mb-1">Pending Approvals</div>
        <div class="text-2xl font-bold">{{ $pendingCount }}: <span class="text-sm font-normal">{{ $pendingCount }} PRs</span></div>
        <div class="text-xs text-gray-400">{{ $pendingCount }} PRs, 6 POs</div>
    </div>
    <div class="bg-green-50 rounded-lg p-4">
        <div class="text-xs text-gray-500 mb-1">Value Awaiting Approval</div>
        <div class="text-2xl font-bold">${{ number_format($valueAwaiting, 0) }}</div>
    </div>
    <div class="bg-orange-50 rounded-lg p-4">
        <div class="text-xs text-gray-500 mb-1">Budget Remaining</div>
        <div class="text-2xl font-bold">${{ number_format($budgetTotal - $budgetUsed, 0) }} <span class="text-sm font-normal text-gray-400">/ ${{ number_format($budgetTotal, 0) }}</span></div>
        <div class="w-full bg-orange-200 rounded-full h-2 mt-2">
            <div class="bg-orange-500 h-2 rounded-full" style="width: {{ round((($budgetTotal - $budgetUsed) / $budgetTotal) * 100) }}%"></div>
        </div>
    </div>
</div>

<div class="grid grid-cols-3 gap-6">

    <div class="col-span-2 bg-white rounded-lg border p-5">
        <div class="flex items-center gap-4 text-sm mb-4">
            <span class="px-2 py-1 rounded bg-brand-light text-brand font-medium">Purchase Requisitions ({{ $requisitions->count() }}) <span class="ml-1 text-xs bg-blue-500 text-white rounded px-1">Active</span></span>
            <span class="text-gray-400">Purchase Orders (6)</span>
            <span class="text-gray-400">History</span>
        </div>

        <table class="w-full text-sm">
            <thead class="text-gray-400 text-xs uppercase border-b">
                <tr>
                    <th class="text-left py-2">Request ID</th>
                    <th class="text-left py-2">Requester</th>
                    <th class="text-left py-2">Department</th>
                    <th class="text-left py-2">Total Amount</th>
                    <th class="text-left py-2">Date Submitted</th>
                    <th class="text-left py-2">Urgency</th>
                    <th class="text-left py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requisitions as $r)
                    <tr class="border-b {{ $selected && $selected->id === $r->id ? 'bg-brand-light/60' : '' }}">
                        <td class="py-3">{{ $r->code }}</td>
                        <td class="py-3">{{ $r->requestor }}</td>
                        <td class="py-3">{{ $r->department }}</td>
                        <td class="py-3">${{ number_format($r->total, 0) }}</td>
                        <td class="py-3">{{ \Carbon\Carbon::parse($r->created_at)->format('d/m') }}</td>
                        <td class="py-3">
                            <span class="text-xs px-2 py-1 rounded {{ \App\Support\Erp::urgencyBadge($r->urgency) }}">{{ $r->urgency }}</span>
                        </td>
                        <td class="py-3 space-x-2 whitespace-nowrap">
                            <a href="{{ route('approvals.index', ['selected' => $r->id]) }}" class="text-xs px-2 py-1 border rounded">View Details</a>
                            <a href="{{ route('approvals.index', ['selected' => $r->id]) }}" class="text-xs px-2 py-1 border rounded bg-brand text-white">Approve</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-6 text-center text-gray-400">No requisitions waiting on approval.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        @if ($selected)
            <div class="bg-white rounded-lg border p-5 sticky top-6">
                <div class="flex justify-between items-start mb-3">
                    <span class="text-xs px-2 py-1 rounded bg-orange-100 text-orange-600 font-medium">{{ \App\Support\Erp::statusLabel($selected->status) }}</span>
                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($selected->created_at)->format('M d, Y') }}</span>
                </div>
                <div class="text-sm mb-4">{{ $selected->requestor }} <span class="text-gray-400">[{{ $selected->department }}]</span></div>

                <h4 class="text-sm font-semibold mb-2">Line items</h4>
                <div class="space-y-1 mb-4">
                    @foreach ($selected->items as $item)
                        <div class="flex justify-between text-sm">
                            <span>{{ $item->item_name }}</span>
                            <span class="text-gray-500">{{ $item->qty }} @ ${{ number_format($item->unit_price, 0) }} = ${{ number_format($item->qty * $item->unit_price, 0) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between font-semibold border-t pt-2 mb-4">
                    <span>Grand total</span><span>${{ number_format($selected->total, 2) }}</span>
                </div>

                <div class="text-sm space-y-1 mb-4">
                    <div class="flex justify-between"><span class="text-gray-500">Budget Impact</span><span>{{ $selected->department }} Q{{ ceil(\Carbon\Carbon::parse($selected->created_at)->month / 3) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Balance</span><span>${{ number_format($budgetTotal - $budgetUsed, 0) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">After Purchase</span><span class="text-green-600">${{ number_format(($budgetTotal - $budgetUsed) - $selected->total, 0) }} (Safe)</span></div>
                </div>

                <h4 class="text-sm font-semibold mb-2">Approval Workflow</h4>
                <div class="space-y-1 mb-4 text-sm">
                    @foreach ($selected->approval_steps as $step)
                        <div class="flex justify-between">
                            <span>Step {{ $step->step_number }}: {{ $step->approver_name ?? $step->role_label }}</span>
                            <span class="text-xs
                                {{ $step->status === 'approved' ? 'text-green-600' : ($step->status === 'rejected' ? 'text-red-600' : 'text-orange-500') }}">
                                {{ ucfirst($step->status) }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <form action="{{ route('approvals.decide', $selected->id) }}" method="POST">
                    @csrf
                    <textarea name="comment" rows="2" placeholder="Comment box"
                              class="w-full border rounded-md px-3 py-2 text-sm mb-3"></textarea>
                    <div class="flex gap-2">
                        <button type="submit" name="action" value="reject"
                                class="flex-1 bg-red-500 hover:bg-red-600 text-white rounded-md py-2 text-sm">Reject</button>
                        <button type="submit" name="action" value="delegate"
                                class="flex-1 border rounded-md py-2 text-sm">Delegate</button>
                        <button type="submit" name="action" value="approve"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white rounded-md py-2 text-sm">Approve Request</button>
                    </div>
                </form>
            </div>
        @else
            <div class="bg-white rounded-lg border p-5 text-sm text-gray-400">
                Select a requisition from the queue to review its details.
            </div>
        @endif

        <a href="{{ route('requisitions.create') }}"
           class="block text-center mt-4 text-sm text-brand border border-brand rounded-md py-2 hover:bg-brand-light">
            + New requisition
        </a>
    </div>
</div>
@endsection
