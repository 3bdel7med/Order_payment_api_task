<?php

namespace App\Http\Resources;

use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'user_id'      => $this->user_id,
            'status'       => $this->status->value,
            'total_amount' => (float) $this->total_amount,
            'items'        => OrderItemResource::collection($this->whenLoaded('items')),
            'payment'      => new PaymentResource($this->whenLoaded('payment')),
            'created_at'   => $this->created_at->toDateTimeString(),
        ];
    }
}
