<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionItem extends Model
{
    protected $fillable = ['requisition_id', 'item_name', 'description', 'qty', 'unit', 'unit_price'];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function getTotalAttribute(): float
    {
        return round($this->qty * $this->unit_price, 2);
    }
}
