<?php

namespace App\Core\Payment\Contracts;

use App\Models\Order;

interface PaymentGatewayInterface
{
    public function pay(Order $order, array $data): array;
}
