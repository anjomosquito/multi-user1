<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Model\Medicines;

class Purchase extends Model
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
    ];
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
