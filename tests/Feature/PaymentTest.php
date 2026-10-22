<?php

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_process_payment_for_confirmed_order_successfully()
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id'      => $user->id,
            'status'       => OrderStatusEnum::CONFIRMED,
            'total_amount' => 150.00,
        ]);

        $response = $this->actingAs($user, 'api')->postJson('/api/payments/process', [
            'order_id'       => $order->id,
            'payment_method' => PaymentMethodEnum::CREDIT_CARD->value,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'payment_id',
                    'order_id',
                    'amount',
                    'status',
                    'payment_method',
                ]
            ]);

        $this->assertDatabaseHas('payments', [
            'order_id'       => $order->id,
            'status'         => PaymentStatusEnum::SUCCESSFUL->value,
            'payment_method' => PaymentMethodEnum::CREDIT_CARD->value,
        ]);
    }
    public function test_cannot_pay_an_order_that_is_already_paid(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id'      => $user->id,
            'status'       => OrderStatusEnum::CONFIRMED,
            'total_amount' => 150.00,
        ]);

        Payment::create([
            'order_id'       => $order->id,
            'payment_id'     => 'CC-ALREADYPAID',
            'amount'         => 150.00,
            'status'         => PaymentStatusEnum::SUCCESSFUL,
            'payment_method' => PaymentMethodEnum::CREDIT_CARD,
        ]);

        $this->actingAs($user, 'api')->postJson('/api/payments/process', [
            'order_id'       => $order->id,
            'payment_method' => PaymentMethodEnum::CREDIT_CARD->value,
        ])->assertStatus(422)
        ->assertJson(['message' => 'This order has already been paid.']);

        $this->assertDatabaseCount('payments', 1);
    }
    public function test_cannot_process_payment_for_unconfirmed_order()
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::PENDING,
            'total_amount' => 150.00,
        ]);

        $response = $this->actingAs($user, 'api')->postJson('/api/payments/process', [
            'order_id' => $order->id,
            'payment_method' => PaymentMethodEnum::CREDIT_CARD->value,
        ]);

        $response->assertStatus(422);
    }
}
