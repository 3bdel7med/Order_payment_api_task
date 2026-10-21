<?php

namespace App\Http\Requests\Payment;

use App\Enums\PaymentMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id'       => ['required', 'integer', 'exists:orders,id'],
            'payment_method' => ['required', 'string', new Enum(PaymentMethodEnum::class)],
        ];
    }
}
