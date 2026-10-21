<?php

namespace App\Core\Payment\Strategies;

use App\Core\Payment\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use Illuminate\Support\Str;

class CreditCardPayment implements PaymentGatewayInterface
{
    public function pay(Order $order, array $data): array
    {
        $apiKey = config('services.credit_card.api_key');
        $secret = config('services.credit_card.secret');
        if (empty($apiKey) || empty($secret)) {
            throw new \Exception("Credit Card gateway credentials are not properly configured.", 500);
        }
        return [
            'payment_id' => 'CC-' . strtoupper(Str::random(10)),
            'amount'     => $order->total_amount,
            'status'     => PaymentStatusEnum::SUCCESSFUL,
        ];
    }
}
