<?php

namespace App\Http\Controllers\Auth\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant\Restaurant;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OperatorRestaurantController extends Controller
{
    public function pending(): JsonResponse
    {
        return response()->json(
            Restaurant::where('status', 'pending')->get()
        );
    }

    public function approved(Restaurant $restaurant,TelegramService $telegramService): JsonResponse
    {
        if ($restaurant->status !== 'pending') {
            return response()->json([
                'message' => 'این درخواست قبلاً بررسی شده است.'
            ], 409);
        }
        $restaurant->update([
            'status' => 'approved'
        ]);

        $telegramService->sendMessage("درخواست ثبت رستوران شما تایید شد.");
        return response()->json(['message' => 'تایید شد.'], 200);
    }

    public function rejected(Restaurant $restaurant,TelegramService $telegramService): JsonResponse
    {
        $restaurant->update([
            'status' => 'rejected'
        ]);
         Restaurant::where('status', 'rejected')->delete();

        $telegramService->sendMessage('درخواست ثبت رستوران شما رد شد.');

        return response()->json(['message' => ' رد شد.'], 422);

    }
}
