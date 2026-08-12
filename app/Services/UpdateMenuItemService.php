<?php

namespace App\Services;

use App\DTOs\UpdateMenuItem;
use App\Exceptions\MenuItemNotInRestaurantException;
use App\Exceptions\RestaurantNotApprovedException;
use App\Models\Menu\Menu;
use App\Models\Restaurant\Restaurant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateMenuItemService
{
    public function execute(UpdateMenuItem $updateMenuItem, Restaurant $restaurant, Menu $menuItem)
    {
        if ($menuItem->restaurant_id != $restaurant->id) {
            throw new MenuItemNotInRestaurantException;
        }
        if ($restaurant->status !== 'approved') {
            throw new RestaurantNotApprovedException;
        }
        DB::transaction(function () use ($updateMenuItem, $menuItem) {

            $updateData = [
                'name' => $updateMenuItem->name,
                'description' => $updateMenuItem->description,
                'category' => $updateMenuItem->category,
                'is_available' => $updateMenuItem->is_available,
                'price' => $updateMenuItem->price,
            ];

            if ($updateMenuItem->image !== null) {
                $oldImage = $menuItem->image;

                $path = $updateMenuItem->image->store('itemsPic', 'public');

                DB::afterRollBack(fn () => Storage::disk('public')->delete($path));

                $updateData['image'] = $path;

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
