<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['bill_id', 'type_name', 'previous_reading', 'current_reading', 'rate', 'amount', 'description'])]
class BillType extends Model
{
    public function bills()
    {
        return $this->belongsTo(Bill::class);
    }
}
