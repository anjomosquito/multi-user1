<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'lprice', 'mprice', 'hprice', 'quantity', 'dosage', 'expdate'];

    // Add search scope
    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('dosage', 'LIKE', "%{$term}%");
    }
}
