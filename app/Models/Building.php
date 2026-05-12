<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['building_name', 'address', 'phone', 'created_by'])]
class Building extends Model
{
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function floors()
    {
        return $this->hasMany(Floor::class);
    }
}
