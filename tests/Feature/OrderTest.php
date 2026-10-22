<?php

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_order_successfully()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/orders', [
            'items' => [
                [
                    'product_name' => 'Laptop',
                    'quantity' => 1,
                    'price' => 1000.00,
                ],
                [
                    'product_name' => 'Mouse',
                    'quantity' => 2,
                    'price' => 50.00,
                ]
            ]
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'status',
                    'total_amount',
                    'items',
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => 1100.00,
        ]);
    }

    public function test_user_can_view_orders_list()
    {
        $user = User::factory()->create();

        Order::create([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::PENDING,
            'total_amount' => 500.00,
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'status', 'total_amount', 'items']
                ],
                'meta'
            ]);
    }

    public function test_user_can_update_order_status()
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::PENDING,
            'total_amount' => 200.00,
        ]);

        $response = $this->actingAs($user, 'api')->putJson("/api/orders/{$order->id}", [
            'status' => OrderStatusEnum::CONFIRMED->value,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatusEnum::CONFIRMED->value,
        ]);
    }

    public function test_user_cannot_delete_order_with_associated_payment()
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::CONFIRMED,
            'total_amount' => 300.00,
        ]);

        $order->payment()->create([
            'payment_id' => 'PAY-12345',
            'amount' => 300.00,
            'status' => 'successful',
            'payment_method' => 'credit_card',
        ]);

        $response = $this->actingAs($user, 'api')->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(422);

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }
}
