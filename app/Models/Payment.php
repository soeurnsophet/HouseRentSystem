<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['bill_id', 'payment_date', 'payment_method', 'amount', 'created_by'])]
class Payment extends Model
{
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
