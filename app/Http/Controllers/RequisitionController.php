<?php

namespace App\Http\Controllers;

use App\Support\Erp;
use Illuminate\Http\Request;

class RequisitionController extends Controller
{
    /**
     * Screen 3: "Create Purchase Requisition"
     */
    public function create()
    {
        return view('requisitions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'requestor'   => 'required|string|max:255',
            'department'  => 'required|string|max:255',
            'needed_by'   => 'required|date',
            'purpose'     => 'nullable|string',
            'items'       => 'required|array|min:1',
            'items.*.item_name'   => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.qty'         => 'required|integer|min:1',
            'items.*.unit'        => 'nullable|string',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        $subtotal = collect($data['items'])->sum(fn ($i) => $i['qty'] * $i['unit_price']);

        $requisition = [
            'id'            => Erp::nextId(),
            'code'          => Erp::nextCode(),
            'title'         => $data['title'],
            'requestor'     => $data['requestor'],
            'department'    => $data['department'],
            'purpose'       => $data['purpose'] ?? null,
            'needed_by'     => $data['needed_by'],
            'created_at'    => now()->format('Y-m-d H:i:s'),
            'subtotal'      => $subtotal,
            'tax_rate'      => 0,
            'total'         => $subtotal,
            'urgency'       => 'Medium',
            'workflow_type' => 'sequential',
            'current_step'  => 1,
            'status'        => 'draft',
            'items'         => array_values($data['items']),
            'approval_steps' => [],
        ];

        Erp::save($requisition);

        // Next: define who needs to approve this requisition (Screen 2)
        return redirect()->route('requisitions.route.edit', $requisition['id']);
    }

    /**
     * Screen 2: "Route Requisition for Approval"
     */
    public function routeEdit(int $id)
    {
        $requisition = Erp::find($id);
        abort_unless($requisition, 404);

        return view('requisitions.route', [
            'requisition' => Erp::obj($requisition),
        ]);
    }

    public function routeStore(Request $request, int $id)
    {
        $requisition = Erp::find($id);
        abort_unless($requisition, 404);

        $data = $request->validate([
            'workflow_type'         => 'required|in:sequential,parallel',
            'steps'                 => 'required|array|min:1',
            'steps.*.role_label'    => 'required|string',
            'steps.*.approver_name' => 'nullable|string',
            'steps.*.required'      => 'nullable|boolean',
        ]);

        $steps = [];
        foreach (array_values($data['steps']) as $i => $step) {
            $steps[] = [
                'step_number'   => $i + 1,
                'role_label'    => $step['role_label'],
                'approver_name' => $step['approver_name'] ?? null,
                'required'      => (bool) ($step['required'] ?? true),
                'status'        => 'pending',
                'comment'       => null,
            ];
        }

        $requisition['approval_steps'] = $steps;
        $requisition['workflow_type']  = $data['workflow_type'];
        $requisition['current_step']   = 1;
        $requisition['status']         = 'pending_approval';

        Erp::save($requisition);

        // Next: it lands in the approval queue (Screen 1)
        return redirect()->route('approvals.index')
            ->with('success', "{$requisition['code']} submitted for approval.");
    }
}
