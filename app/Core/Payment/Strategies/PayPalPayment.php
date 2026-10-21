<?php

namespace App\Core\Payment\Strategies;

use App\Core\Payment\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use Exception;
use Illuminate\Support\Str;

class PayPalPayment implements PaymentGatewayInterface
{
    public function pay(Order $order, array $data): array
    {
        $apiKey = config('services.paypal.client_id');
        $secret = config('services.paypal.secret');
        if (empty($apiKey) || empty($secret)) {
            throw new Exception("PayPal gateway credentials are not properly configured.", 500);
        }
        return [
            'payment_id' => 'PAYPAL-' . strtoupper(Str::random(10)),
            'amount'     => $order->total_amount,
            'status'     => PaymentStatusEnum::SUCCESSFUL,
        ];
    }
}
