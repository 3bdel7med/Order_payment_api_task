<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\ProcessPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    public function index(Request $request): JsonResponse
    {
        $orderId = $request->query('order_id');
        $payments = $this->paymentService->getPayments($orderId ? (int) $orderId : null);

        return response()->json([
            'data' => PaymentResource::collection($payments),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page'    => $payments->lastPage(),
                'per_page'     => $payments->perPage(),
                'total'        => $payments->total(),
            ]
        ]);
    }

    public function process(ProcessPaymentRequest $request): JsonResponse
    {
        try {
            $order = Order::findOrFail($request->validated('order_id'));

            $payment = $this->paymentService->processPayment(
                $order,
                $request->validated('payment_method')
            );

            return response()->json([
                'message' => 'Payment processed successfully',
                'data'    => new PaymentResource($payment),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }
}
