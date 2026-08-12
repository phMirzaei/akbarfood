<?php

namespace App\Services;

use App\DTOs\RejectRestaurant;
use App\Models\Restaurant\Restaurant;
use Illuminate\Support\Facades\DB;

class RejectRestaurantService
{
    public function __construct(
        private NotificationService $notificationService,
    ) {}

    public function execute(RejectRestaurant $rejectRestaurant)
    {
        $restaurant = Restaurant::findOrFail($rejectRestaurant->restaurantId);
        if (! $restaurant->isPending()) {
            throw new \DomainException('این درخواست قبلاً بررسی شده است.');
        }
        DB::transaction(function () use ($restaurant) {
            $owner = $restaurant->owner()->first();
            $restaurant->delete();
            DB::afterCommit(function () use ($owner) {
                $this->notificationService->send($owner->phone, 'درخواست ثبت رستوران شما رد شد.');
            });
        });
    }
}
