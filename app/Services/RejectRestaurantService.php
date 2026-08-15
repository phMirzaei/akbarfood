<?php

namespace App\Services;

use App\DTOs\RejectRestaurant;
use App\Exceptions\UnauthorizedException;
use App\Models\Restaurant\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RejectRestaurantService
{
    public function __construct(
        private NotificationService $notificationService,
    ) {}

    public function execute(RejectRestaurant $rejectRestaurant)
    {
        $operator = User::findOrFail($rejectRestaurant->actorId);
        if (! $operator->isOperator() && ! $operator->isAdmin()) {
            throw new UnauthorizedException;
        }
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
