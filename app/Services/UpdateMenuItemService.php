<?php

namespace App\Services;

use App\DTOs\UpdateMenuItem;
use App\Exceptions\MenuItemNotInRestaurantException;
use App\Exceptions\RestaurantNotApprovedException;
use App\Exceptions\UnauthorizedException;
use App\Models\Menu\Menu;
use App\Models\Restaurant\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateMenuItemService
{
    public function execute(UpdateMenuItem $updateMenuItem): void
    {
        DB::transaction(function () use ($updateMenuItem) {

            if ($updateMenuItem->imagePath !== null) {
                DB::afterRollBack(
                    fn () => Storage::disk('public')->delete($updateMenuItem->imagePath)
                );
            }
            $owner = User::findOrFail($updateMenuItem->actorId);
            if (! $owner->isOwner()) {
                throw new UnauthorizedException;
            }
            $restaurant = Restaurant::findOrFail(
                $updateMenuItem->restaurantId
            );

            $menuItem = Menu::findOrFail(
                $updateMenuItem->menuId
            );

            if ($menuItem->restaurant_id != $restaurant->id) {
                throw new MenuItemNotInRestaurantException;
            }

            if (! $restaurant->isApproved()) {
                throw new RestaurantNotApprovedException;
            }

            $updateData = [
                'name' => $updateMenuItem->name,
                'description' => $updateMenuItem->description,
                'category' => $updateMenuItem->category,
                'is_available' => $updateMenuItem->is_available,
                'price' => $updateMenuItem->price,
            ];

            if ($updateMenuItem->imagePath !== null) {
                $oldImage = $menuItem->image;

                $updateData['image'] = $updateMenuItem->imagePath;

                DB::afterCommit(function () use ($oldImage) {
                    if ($oldImage) {
                        Storage::disk('public')->delete($oldImage);
                    }
                });
            }

            $menuItem->update($updateData);
        });
    }
}
