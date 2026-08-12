<?php

namespace App\Services;

use App\DTOs\ApproveRestaurant;
use App\Models\Restaurant\Restaurant;

class ApproveRestaurantService
{
    public function __construct(
        private NotificationService $notificationService) {}

    public function execute(ApproveRestaurant $approveRestaurant)
    {
        $restaurant = Restaurant::findOrFail($approveRestaurant->restaurantId);
        if (! $restaurant->isPending()) {
            throw new \DomainException('این درخواست قبلاً بررسی شده است.');
        }
        $restaurant->approve();
        $owner = $restaurant->owner()->first();
        $this->notificationService->send($owner->phone, 'درخواست ثبت رستوران شما تایید شد.');
    }
}
