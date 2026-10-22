<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Exception;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        $statusParam = $request->query('status');
        $status = $statusParam ? OrderStatusEnum::tryFrom($statusParam) : null;

        $orders = $this->orderService->getOrders($status);

        return response()->json([
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ]
        ]);
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder(
            $request->user()->id,
            $request->validated('items')
        );

        return response()->json([
            'message' => 'Order created successfully',
            'data'    => new OrderResource($order),
        ], 201);
    }

    public function show(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);
        return response()->json([
            'data' => new OrderResource($order->load(['items', 'payment'])),
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $updatedOrder = $this->orderService->updateOrder($order, $request->validated());
        Gate::authorize('update', $order);
        return response()->json([
            'message' => 'Order updated successfully',
            'data'    => new OrderResource($updatedOrder),
        ]);
    }

    public function destroy(Order $order): JsonResponse
    {
        try {
            Gate::authorize('delete', $order);
            $this->orderService->deleteOrder($order);

            return response()->json([
                'message' => 'Order deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }
}
