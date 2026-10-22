<?php

namespace App\Policies;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }

   public function update(User $user, Order $order): bool
     {
        return $user->id === $order->user_id
             && $order->status !== OrderStatusEnum::CANCELLED;
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }
     public function pay(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }
}
