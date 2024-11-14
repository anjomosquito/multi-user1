<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
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
        'purchase_date',
        'status',
        'confirmed_at',
        'confirmed_by',
        'ready_for_pickup',
        'pickup_ready_at',
        'admin_pickup_verified',
        'user_pickup_verified',
        'admin_verified_at',
        'user_verified_at',
        'pickup_deadline'
    ];

    // Define status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';

    // Cast attributes to their proper types
    protected $casts = [
        'ready_for_pickup' => 'boolean',
        'pickup_ready_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'purchase_date' => 'datetime',
        'expdate' => 'date',
        'quantity' => 'integer',
        'lprice' => 'decimal:2',
        'mprice' => 'decimal:2',
        'hprice' => 'decimal:2',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // Status check methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isReadyForPickup(): bool
    {
        return $this->ready_for_pickup;
    }

    public function isVerifiedByUser(): bool
    {
        return $this->user_pickup_verified;
    }

    public function isVerifiedByAdmin(): bool
    {
        return $this->admin_pickup_verified;
    }

    public function isFullyVerified(): bool
    {
        return $this->user_pickup_verified && $this->admin_pickup_verified;
    }

    // Status update methods
    public function markAsConfirmed(): void
    {
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_at' => now(),
            'confirmed_by' => auth()->id()
        ]);
    }

    public function markAsReadyForPickup(): void
    {
        $this->update([
            'ready_for_pickup' => true,
            'pickup_ready_at' => now()
        ]);
    }

    // Query scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeReadyForPickup($query)
    {
        return $query->where('ready_for_pickup', true);
    }

    public function scopeNotReadyForPickup($query)
    {
        return $query->where('ready_for_pickup', false);
    }
}
