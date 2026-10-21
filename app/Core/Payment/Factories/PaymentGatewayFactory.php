<?php

namespace App\Core\Payment\Factories;

use App\Core\Payment\Contracts\PaymentGatewayInterface;
use App\Core\Payment\Strategies\CreditCardPayment;
use App\Core\Payment\Strategies\PayPalPayment;
use App\Enums\PaymentMethodEnum;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    public static function make(string $method): PaymentGatewayInterface
    {
        $paymentMethod = PaymentMethodEnum::tryFrom($method);

        return match ($paymentMethod) {
            PaymentMethodEnum::CREDIT_CARD => new CreditCardPayment(),
            PaymentMethodEnum::PAYPAL      => new PayPalPayment(),
            default                        => throw new InvalidArgumentException(" payment method [{$method}] is not supported."),
        };
    }
}
