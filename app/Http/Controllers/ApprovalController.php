<?php

namespace App\Http\Controllers;

use App\Support\Erp;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    /**
     * Screen 1: "PR & PO Approval Queue"
     */
    public function index(Request $request)
    {
        $requisitions = collect(Erp::all())
            ->where('status', 'pending_approval')
            ->sortByDesc('created_at')
            ->values();

        $pendingCount  = $requisitions->count();
        $valueAwaiting = $requisitions->sum('total');
        $budgetTotal   = 50000;
        $budgetUsed    = $budgetTotal - 12500; // demo figure, matches "$12,500 / $50,000"

        $selectedId = $request->integer('selected') ?: ($requisitions->first()['id'] ?? null);
        $selected   = $selectedId ? $requisitions->firstWhere('id', $selectedId) : null;

        return view('approvals.index', [
            'requisitions'  => $requisitions->map(fn ($r) => Erp::obj($r)),
            'selected'      => $selected ? Erp::obj($selected) : null,
            'pendingCount'  => $pendingCount,
            'valueAwaiting' => $valueAwaiting,
            'budgetTotal'   => $budgetTotal,
            'budgetUsed'    => $budgetUsed,
        ]);
    }

    public function decide(Request $request, int $id)
    {
        $requisition = Erp::find($id);
        abort_unless($requisition, 404);

        $data = $request->validate([
            'action'  => 'required|in:approve,reject,delegate',
            'comment' => 'nullable|string',
        ]);

        $verb = ['approve' => 'approved', 'reject' => 'rejected', 'delegate' => 'delegated'][$data['action']];

        foreach ($requisition['approval_steps'] as &$step) {
            if ($step['step_number'] === $requisition['current_step']) {
                $step['status']  = $verb;
                $step['comment'] = $data['comment'] ?? null;
            }
        }
        unset($step);

        if ($data['action'] === 'reject') {
            $requisition['status'] = 'rejected';
        } elseif ($data['action'] === 'approve') {
            $nextStepNumber = $requisition['current_step'] + 1;
            $hasNext = collect($requisition['approval_steps'])->firstWhere('step_number', $nextStepNumber);

            if ($hasNext) {
                $requisition['current_step'] = $nextStepNumber;
            } else {
                $requisition['status'] = 'approved';
            }
        }
        // delegate: leaves status/current_step as-is, just logs the delegation on the step

        Erp::save($requisition);

        return redirect()->route('approvals.index')
            ->with('success', "{$requisition['code']} was {$verb}.");
    }
}
