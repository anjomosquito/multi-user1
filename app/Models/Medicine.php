<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Medicine extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['name', 'lprice', 'mprice', 'hprice', 'quantity', 'dosage', 'expdate'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'quantity', 'lprice', 'mprice', 'hprice', 'dosage', 'expdate'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Add search scope
    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('dosage', 'LIKE', "%{$term}%");
    }
}
