<?php

namespace App\Services;

use App\Core\Payment\Factories\PaymentGatewayFactory;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentService
{
    /**
     * payment for a specific order using the specified payment method and details.
     */
    public function processPayment(Order $order, string $paymentMethod, array $paymentDetails = []): Payment
    {
        // Business Rule: payments can only be processed for orders in confirmed status
        if (!$order->isConfirmed()) {
            throw new Exception("Payments can only be processed for orders in confirmed status.", 422);
        }

        // check if the order already has a successful payment
        if ($order->hasAssociatedPayment() && $order->payment->isSuccessful()) {
            throw new Exception("This order has already been paid.", 422);
        }


        $gateway = PaymentGatewayFactory::make($paymentMethod);

        $result = $gateway->pay($order, $paymentDetails);

        // Save the Payment record in the database
        return DB::transaction(function () use ($order, $paymentMethod, $result) {
            return Payment::create([
                'order_id'       => $order->id,
                'payment_id'     => $result['payment_id'],
                'amount'         => $result['amount'],
                'status'         => $result['status'],
                'payment_method' => $paymentMethod,
            ]);
        });
    }

    /**
     * get all payments or for a specific order
     */
    public function getPayments(?int $orderId = null, int $perPage = 15)
    {
        return Payment::with('order')
            ->whereHas('order', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->when($orderId, fn($query) => $query->where('order_id', $orderId))
            ->latest()
            ->paginate($perPage);
    }
}
