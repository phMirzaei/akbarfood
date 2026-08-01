<?php

namespace App\Http\Controllers\Payment;

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
        $requestPaymentService->execute($order);

        return response()->json([
            'message' => 'در حال ارسال شما به درگاه پرداخت...',
        ]);
    }

    public function verifyPayment(VerifyPaymentService $verifyPaymentService, Payment $payment): JsonResponse
    {
        $payment = $verifyPaymentService->execute($payment);

        return response()->json([
            'message' => 'سفارش شما با موفقیت پرداخت شد.',
            'سفارش' => $payment,

        ]);

    }
}
