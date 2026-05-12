<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['room_id', 'tenant_id', 'start_date', 'end_date', 'status', 'created_by'])]
class Booking extends Model
{
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}
