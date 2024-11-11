<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'medicine_id',
        'quantity',
        'name',
        'lprice',
        'mprice',
        'hprice',
        'dosage',
        'expdate',
        'username',
    ];

    // Relationship to the User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to the Medicine
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
