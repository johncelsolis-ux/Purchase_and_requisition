<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'title', 'requestor', 'department', 'purpose', 'needed_by',
        'subtotal', 'tax_rate', 'total', 'urgency', 'workflow_type',
        'current_step', 'status',
    ];

    protected $casts = [
        'needed_by' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function approvalSteps()
    {
        return $this->hasMany(ApprovalStep::class)->orderBy('step_number');
    }

    public function currentApprovalStep()
    {
        return $this->approvalSteps()->where('step_number', $this->current_step)->first();
    }

    public function urgencyColor(): string
    {
        return match ($this->urgency) {
            'High' => 'bg-red-500 text-white',
            'Medium' => 'bg-orange-400 text-white',
            default => 'bg-green-500 text-white',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending_approval' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Draft',
        };
    }
}
