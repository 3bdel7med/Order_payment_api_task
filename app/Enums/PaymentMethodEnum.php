<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case CREDIT_CARD = 'credit_card';
    case PAYPAL = 'paypal';
}
