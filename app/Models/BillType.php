<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['type_name', 'rate', 'description'])]
class BillType extends Model
{
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}
