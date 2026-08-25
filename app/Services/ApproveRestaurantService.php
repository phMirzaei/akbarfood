<?php

namespace App\Services;

use App\DTOs\ApproveRestaurant;
use App\Exceptions\UnauthorizedException;
use App\Jobs\SendNotification;
use App\Models\Restaurant\Restaurant;
use App\Models\User;

class ApproveRestaurantService
{
    public function execute(ApproveRestaurant $approveRestaurant)
    {
        $operator = User::findOrFail($approveRestaurant->actorId);
        if (! $operator->isOperator() && ! $operator->isAdmin()) {
            throw new UnauthorizedException;
        }
        $restaurant = Restaurant::findOrFail($approveRestaurant->restaurantId);
        if (! $restaurant->isPending()) {
            throw new \DomainException('این درخواست قبلاً بررسی شده است.');
        }
        $restaurant->approve();
        $restaurant->save();
        $owner = $restaurant->owner()->first();
        SendNotification::dispatch($owner->phone, 'درخواست ثبت رستوران شما تایید شد.');
    }
}
