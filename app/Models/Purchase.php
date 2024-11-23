<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Notifications\PurchaseNotification;
use Illuminate\Support\Facades\Log;

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
        'pickup_deadline',
        'payment_proof',
        'payment_status',
        'payment_verified_at',
        'payment_verified_by',
        'transaction_id'
    ];

    // Define status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';

    // Add status constants
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_VERIFIED = 'verified';
    const PAYMENT_STATUS_REJECTED = 'rejected';

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

    // Add payment status check methods
    public function isPaymentPending(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PENDING;
    }

    public function isPaymentVerified(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_VERIFIED;
    }

    public function isPaymentRejected(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_REJECTED;
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

    // Add these to the $appends array to make them available in JSON
    protected $appends = [
        'payment_proof_url'
    ];

    // Add this accessor method
    public function getPaymentProofUrlAttribute()
    {
        if (!$this->payment_proof) {
            return null;
        }
        return url('storage/' . $this->payment_proof);
    }

    public function determineStatus()
    {
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }
        if ($this->status === 'completed' || ($this->user_pickup_verified && $this->admin_pickup_verified)) {
            return 'completed';
        }
        if ($this->user_pickup_verified && !$this->admin_pickup_verified) {
            return 'verified';
        }
        if ($this->ready_for_pickup) {
            return 'ready_for_pickup';
        }
        if ($this->status === 'confirmed') {
            return 'confirmed';
        }
        return 'pending';
    }

    // Add this method to determine pickup status
    public function getPickupStatusAttribute()
    {
        if ($this->user_pickup_verified && $this->admin_pickup_verified) {
            return 'Done';
        }
        if ($this->ready_for_pickup) {
            return 'Ready for Pickup';
        }
        return 'Not Ready Yet';
    }

    // Add this method for sending notifications
    public function sendNotification($type)
    {
        try {
            // Get the user's email from the relationship
            $user = $this->user;
            
            if (!$user || !$user->email) {
                Log::error('Failed to send notification: No valid user email', [
                    'purchase_id' => $this->id,
                    'user_id' => $this->user_id
                ]);
                return false;
            }

            // Send notification to the user
            $user->notify(new PurchaseNotification($this, $type));

            Log::info('Notification sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'type' => $type
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage(), [
                'user_id' => $this->user_id,
                'email' => $this->user->email ?? 'unknown',
                'purchase_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
