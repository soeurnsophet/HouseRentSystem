<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['type_name', 'description'])]
class RoomType extends Model
{
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
