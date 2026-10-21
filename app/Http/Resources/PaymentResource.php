<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'payment_id'     => $this->payment_id,
            'order_id'       => $this->order_id,
            'amount'         => (float) $this->amount,
            'status'         => $this->status->value,
            'payment_method' => $this->payment_method->value,
            'created_at'     => $this->created_at->toDateTimeString(),
        ];
    }
}
