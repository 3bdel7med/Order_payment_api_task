<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    public function createOrder(int $userId, array $itemsData): Order
    {
        return DB::transaction(function () use ($userId, $itemsData) {
            $totalAmount = 0;

            foreach ($itemsData as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }
            $order = Order::create([
                'user_id'      => $userId,
                'status'       => OrderStatusEnum::PENDING,
                'total_amount' => $totalAmount,
            ]);

            $order->items()->createMany($itemsData);

            return $order->load('items');
        });
    }


    public function getOrders(?OrderStatusEnum $status = null, int $perPage = 15): LengthAwarePaginator
    {
        return Order::with(['items', 'payment'])
            ->when($status, fn($query) => $query->status($status))
            ->latest()
            ->paginate($perPage);
    }


    public function updateOrder(Order $order, array $data): Order
    {
        $order->update($data);
        return $order;
    }


    public function deleteOrder(Order $order): bool
    {
        // Business Rule: cannot delete an order if it has associated payment transactions
        if ($order->hasAssociatedPayment()) {
            throw new Exception("Cannot delete order with associated payment transactions.", 422);
        }

        return $order->delete();
    }
}
