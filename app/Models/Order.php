<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'status', 'total', 'meta'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'total' => 'decimal:2',
            'status' => OrderStatus::class,
        ];
    }

    /**
     * Get the order items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payments for this order.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the order is pending.
     */
    public function isPending(): bool
    {
        return $this->status === OrderStatus::PENDING;
    }

    /**
     * Check if the order is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === OrderStatus::CONFIRMED;
    }

    /**
     * Check if the order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::CANCELLED;
    }

    /**
     * Check if the order has any payments.
     */
    public function hasPayments(): bool
    {
        return $this->payments()->exists();
    }

    /**
     * Calculate total price from order items.
     */
    public function calculateTotal(): float
    {
        return $this->items()->sum('total_price');
    }

    /**
     * Check if the order can be paid.
     */
    public function canBePaid(): bool
    {
        return $this->isConfirmed() && $this->total > 0;
    }

    /**
     * Check if the order can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return ! $this->hasPayments() && in_array($this->status, [
            OrderStatus::PENDING,
            OrderStatus::CONFIRMED,
        ]);
    }

    /**
     * Check if the order can be updated.
     */
    public function canBeUpdated(): bool
    {
        return ! $this->hasPayments() && $this->status === OrderStatus::PENDING;
    }

    /**
     * Check if the order can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return ! $this->hasPayments();
    }

    /**
     * Check if the order has successful payments.
     */
    public function hasSuccessfulPayments(): bool
    {
        return $this->payments()
            ->where('status', PaymentStatus::SUCCESSFUL)
            ->exists();
    }

    /**
     * Get the total amount of successful payments.
     */
    public function getSuccessfulPaymentsTotal(): float
    {
        return $this->payments()
            ->where('status', PaymentStatus::SUCCESSFUL)
            ->sum('amount');
    }

    /**
     * Check if the order is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->hasSuccessfulPayments() &&
               $this->getSuccessfulPaymentsTotal() >= $this->total;
    }
}
