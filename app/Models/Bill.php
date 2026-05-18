<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['booking_id', 'amount', 'bill_date', 'created_by'])]
class Bill extends Model
{
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function billType()
    {
        return $this->hasMany(BillType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
