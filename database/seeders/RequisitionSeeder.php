<?php

namespace Database\Seeders;

use App\Models\ApprovalStep;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use Illuminate\Database\Seeder;

class RequisitionSeeder extends Seeder
{
    public function run(): void
    {
        // PR-2026-090 · Johny papa · Marketing · High urgency · mid-approval (matches Image 1 detail panel)
        $r1 = Requisition::create([
            'code' => 'PR-2026-090',
            'title' => 'Premium SaaS + Onboarding Licenses',
            'requestor' => 'Johny papa',
            'department' => 'Marketing',
            'purpose' => 'Marketing team tooling for Q2 campaign onboarding.',
            'needed_by' => '2026-07-15',
            'subtotal' => 12500,
            'tax_rate' => 0,
            'total' => 12500,
            'urgency' => 'High',
            'workflow_type' => 'sequential',
            'current_step' => 2,
            'status' => 'pending_approval',
        ]);

        RequisitionItem::create([
            'requisition_id' => $r1->id,
            'item_name' => 'Premium SaaS',
            'description' => 'Annual seats',
            'qty' => 10, 'unit' => 'seat', 'unit_price' => 1200,
        ]);
        RequisitionItem::create([
            'requisition_id' => $r1->id,
            'item_name' => 'Onboarding Licenses',
            'description' => 'New hire onboarding',
            'qty' => 5, 'unit' => 'license', 'unit_price' => 100,
        ]);

        ApprovalStep::create(['requisition_id' => $r1->id, 'step_number' => 1, 'role_label' => 'Manager Approval', 'approver_name' => 'Sarah Jenkins', 'status' => 'approved', 'decided_at' => now()->subDay()]);
        ApprovalStep::create(['requisition_id' => $r1->id, 'step_number' => 2, 'role_label' => 'Department Head Approval', 'approver_name' => 'You (Dept Head)', 'status' => 'pending']);
        ApprovalStep::create(['requisition_id' => $r1->id, 'step_number' => 3, 'role_label' => 'Finance Approval', 'approver_name' => 'Finance', 'status' => 'pending']);

        // PR-2026-090 · Jane Doe · Eng · Medium urgency
        $r2 = Requisition::create([
            'code' => 'PR-2026-091',
            'title' => 'Dev tooling licenses',
            'requestor' => 'Jane Doe',
            'department' => 'Eng',
            'purpose' => 'Engineering tooling renewal.',
            'needed_by' => '2026-07-20',
            'subtotal' => 4200, 'tax_rate' => 0, 'total' => 4200,
            'urgency' => 'Medium', 'workflow_type' => 'sequential',
            'current_step' => 1, 'status' => 'pending_approval',
        ]);
        ApprovalStep::create(['requisition_id' => $r2->id, 'step_number' => 1, 'role_label' => 'Manager Approval', 'approver_name' => 'Manager', 'status' => 'pending']);

        // PR-2026-090 · Robin lapa · Ops · Low urgency
        $r3 = Requisition::create([
            'code' => 'PR-2026-092',
            'title' => 'Office supplies',
            'requestor' => 'Robin lapa',
            'department' => 'Ops',
            'purpose' => 'Routine office supplies restock.',
            'needed_by' => '2026-07-10',
            'subtotal' => 850, 'tax_rate' => 0, 'total' => 850,
            'urgency' => 'Low', 'workflow_type' => 'sequential',
            'current_step' => 1, 'status' => 'pending_approval',
        ]);
        ApprovalStep::create(['requisition_id' => $r3->id, 'step_number' => 1, 'role_label' => 'Manager Approval', 'approver_name' => 'Manager', 'status' => 'pending']);
    }
}
