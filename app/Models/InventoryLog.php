<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'admin_id',
        'action_type',
        'quantity_change',
        'old_quantity',
        'new_quantity',
        'old_price',
        'new_price',
        'old_status',
        'new_status',
        'notes'
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
