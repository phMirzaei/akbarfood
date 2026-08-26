<?php

namespace App\Http\Controllers\Payment;

use App\DTOs\RequestPayment;
use App\DTOs\VerifyPayment;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Order\Order;
use App\Models\Payment\Payment;
use App\Services\RequestPaymentService;
use App\Services\VerifyPaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function sendRequestPayment(RequestPaymentService $requestPaymentService, Order $order): JsonResponse
    {
        $requestPayment = new RequestPayment(
            actorId: auth()->id(),
            orderId: $order->id
        );
        $payment = $requestPaymentService->execute($requestPayment);

        return response()->json([
            'message' => 'در حال ارسال شما به درگاه پرداخت...',
            'data' => new PaymentResource($payment),
        ], 201);
    }

    public function verifyPayment(VerifyPaymentService $verifyPaymentService, Payment $payment): JsonResponse
    {
        $verifyPayment = new VerifyPayment(
            actorId: auth()->id(),
            paymentId: $payment->id
        );
        $payment = $verifyPaymentService->execute($verifyPayment);

        return response()->json([
            'message' => 'سفارش شما با موفقیت پرداخت شد.',
            'payment' => new PaymentResource($payment),

        ]);

    }
}
