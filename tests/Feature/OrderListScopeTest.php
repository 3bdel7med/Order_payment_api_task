<?php

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderListScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_list_only_contains_the_authenticated_users_orders(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        foreach ([$owner, $owner, $other] as $user) {
            Order::create([
                'user_id'      => $user->id,
                'status'       => OrderStatusEnum::PENDING,
                'total_amount' => 100,
            ]);
        }

        $this->actingAs($owner, 'api')
            ->getJson('/api/orders')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }
}
