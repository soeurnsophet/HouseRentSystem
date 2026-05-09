<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'gender_kh',
    'gender_kh_full',
    'gender_en',
    'gender_en_full',
])]
class Gender extends Model
{
    /** @use HasFactory<\Database\Factories\GenderFactory> */
    use HasFactory;

    // Relationships user to gender
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
