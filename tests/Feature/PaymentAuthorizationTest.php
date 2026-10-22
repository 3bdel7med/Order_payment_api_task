<?php

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_pay_another_users_order(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $order = Order::create([
            'user_id'      => $owner->id,
            'status'       => OrderStatusEnum::CONFIRMED,
            'total_amount' => 100,
        ]);

        $this->actingAs($intruder, 'api')
            ->postJson('/api/payments/process', [
                'order_id'       => $order->id,
                'payment_method' => 'credit_card',
            ])
            ->assertForbidden();
    }
}
