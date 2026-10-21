<?php

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use App\Enums\PaymentMethodEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_id',
        'amount',
        'status',
        'payment_method',
    ];

    protected $casts = [
        'status'         => PaymentStatusEnum::class,
        'payment_method' => PaymentMethodEnum::class,
        'amount'         => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === PaymentStatusEnum::SUCCESSFUL;
    }
}
