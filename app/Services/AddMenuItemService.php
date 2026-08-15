<?php

namespace App\Services;

use App\DTOs\AddMenuItem;
use App\Exceptions\RestaurantNotApprovedException;
use App\Exceptions\UnauthorizedException;
use App\Models\Menu\Menu;
use App\Models\Restaurant\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AddMenuItemService
{
    public function execute(AddMenuItem $addMenuItem)
    {
        DB::transaction(function () use ($addMenuItem) {

            if ($addMenuItem->imagePath !== null) {
                DB::afterRollBack(
                    fn () => Storage::disk('public')->delete($addMenuItem->imagePath)
                );
            }
            $owner = User::findOrFail($addMenuItem->actorId);
            if (! $owner->restaurants()
                ->where('restaurants.id', $addMenuItem->restaurantId)
                ->wherePivot('role', 'owner')->exists()) {
                throw new UnauthorizedException;
            }
            $restaurant = Restaurant::findOrFail($addMenuItem->restaurantId);

            if (! $restaurant->isApproved()) {
                throw new RestaurantNotApprovedException;
            }

            Menu::create([
                'name' => $addMenuItem->name,
                'description' => $addMenuItem->description,
                'category' => $addMenuItem->category,
                'image' => $addMenuItem->imagePath,
                'is_available' => $addMenuItem->is_available,
                'price' => $addMenuItem->price,
                'restaurant_id' => $restaurant->id,
            ]);
        });
    }
}
