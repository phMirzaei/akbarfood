<?php

namespace App\Http\Controllers\Payment;

use App\DTOs\RequestPayment;
use App\DTOs\VerifyPayment;
use App\Http\Controllers\Controller;
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
            userId: auth()->id(),
            order: $order
        );
        $requestPaymentService->execute($requestPayment);

        return response()->json([
            'message' => 'در حال ارسال شما به درگاه پرداخت...',
        ]);
    }

    public function verifyPayment(VerifyPaymentService $verifyPaymentService, Payment $payment): JsonResponse
    {
        $verifyPayment = new VerifyPayment(
            userId: auth()->id(),
            payment: $payment
        );
        $payment = $verifyPaymentService->execute($verifyPayment);

        return response()->json([
            'message' => 'سفارش شما با موفقیت پرداخت شد.',
            'پرداخت' => $payment,

        ]);

    }
}
