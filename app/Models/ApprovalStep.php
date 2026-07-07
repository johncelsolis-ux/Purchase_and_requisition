<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    protected $fillable = [
        'requisition_id', 'step_number', 'role_label', 'approver_name',
        'required', 'status', 'comment', 'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }
}
