<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['payment_id', 'order_id', 'status', 'method', 'amount', 'payload'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payload' => 'array',
            'status' => PaymentStatus::class,
            'method' => PaymentMethod::class,
        ];
    }

    /**
     * Get the order that owns the payment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if the payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === PaymentStatus::PENDING;
    }

    /**
     * Check if the payment is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === PaymentStatus::SUCCESSFUL;
    }

    /**
     * Check if the payment is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === PaymentStatus::FAILED;
    }
}
