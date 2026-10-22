<?php

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderWriteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_intruder_cannot_update_order_and_nothing_changes(): void
    {
        [$owner, $intruder] = User::factory()->count(2)->create();
        $order = $this->orderFor($owner);

        $this->actingAs($intruder, 'api')
            ->putJson("/api/orders/{$order->id}", ['status' => 'cancelled'])
            ->assertForbidden();

        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => OrderStatusEnum::PENDING->value,
        ]);
    }

    public function test_intruder_cannot_delete_order(): void
    {
        [$owner, $intruder] = User::factory()->count(2)->create();
        $order = $this->orderFor($owner);

        $this->actingAs($intruder, 'api')
            ->deleteJson("/api/orders/{$order->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    private function orderFor(User $user): Order
    {
        return Order::create([
            'user_id'      => $user->id,
            'status'       => OrderStatusEnum::PENDING,
            'total_amount' => 100,
        ]);
    }
}
