<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
    ];

    protected $casts = [
        'status'       => OrderStatusEnum::class,
        'total_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }


    public function scopeStatus(Builder $query, OrderStatusEnum $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatusEnum::PENDING);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', OrderStatusEnum::CONFIRMED);
    }


    public function isConfirmed(): bool
    {
        return $this->status === OrderStatusEnum::CONFIRMED;
    }

    public function hasAssociatedPayment(): bool
    {
        return $this->payment()->exists();
    }
}
