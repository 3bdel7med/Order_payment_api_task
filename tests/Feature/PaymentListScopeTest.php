<?php

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentListScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_payments_list_only_contains_the_authenticated_users_payments(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $ownerOrder = $this->confirmedOrderFor($owner);
        $otherOrder = $this->confirmedOrderFor($other);

        foreach ([$ownerOrder, $otherOrder] as $order) {
            Payment::create([
                'order_id'       => $order->id,
                'payment_id'     => 'PAY-' . $order->id,
                'amount'         => 100,
                'status'         => 'successful',
                'payment_method' => 'credit_card',
            ]);
        }

        $this->actingAs($owner, 'api')
            ->getJson('/api/payments')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.order_id', $ownerOrder->id);
    }

    private function confirmedOrderFor(User $user): Order
    {
        return Order::create([
            'user_id'      => $user->id,
            'status'       => OrderStatusEnum::CONFIRMED,
            'total_amount' => 100,
        ]);
    }
}
