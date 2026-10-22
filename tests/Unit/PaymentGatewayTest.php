<?php

use App\Core\Payment\Factories\PaymentGatewayFactory;
use App\Core\Payment\Strategies\CreditCardPayment;
use App\Core\Payment\Strategies\PaypalPayment;
use InvalidArgumentException;

test('it resolves credit card payment gateway successfully', function () {
    $gateway = PaymentGatewayFactory::make('credit_card');

    expect($gateway)->toBeInstanceOf(CreditCardPayment::class);
});

test('it resolves paypal payment gateway successfully', function () {
    $gateway = PaymentGatewayFactory::make('paypal');

    expect($gateway)->toBeInstanceOf(PaypalPayment::class);
});

test('it throws an exception for unsupported payment gateway', function () {
    PaymentGatewayFactory::make('unsupported_gateway');
})->throws(InvalidArgumentException::class);
