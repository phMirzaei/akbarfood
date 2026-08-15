<?php

namespace App\Services;

use App\DTOs\ApproveRestaurant;
use App\Models\Restaurant\Restaurant;
use App\Models\User;
use Illuminate\Validation\UnauthorizedException;

class ApproveRestaurantService
{
    public function __construct(
        private NotificationService $notificationService) {}

    public function execute(ApproveRestaurant $approveRestaurant)
    {
        $operator = User::findOrFail($approveRestaurant->actorId);
        if (! $operator->isOperator()) {
            throw new UnauthorizedException;
        }
        $restaurant = Restaurant::findOrFail($approveRestaurant->restaurantId);
        if (! $restaurant->isPending()) {
            throw new \DomainException('این درخواست قبلاً بررسی شده است.');
        }
        $restaurant->approve();
        $restaurant->save();
        $owner = $restaurant->owner()->first();
        $this->notificationService->send($owner->phone, 'درخواست ثبت رستوران شما تایید شد.');
    }
}
