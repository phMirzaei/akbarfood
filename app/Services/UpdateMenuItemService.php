<?php

namespace App\Services;

use App\DTOs\UpdateMenuItem;
use App\Exceptions\ModelNotFoundException;
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
            throw new ModelNotFoundException;
        }
        if ($restaurant->status !== 'approved') {
            throw new RestaurantNotApprovedException;
        }
        $updatedData = [
            'name' => $updateMenuItem->name,
            'description' => $updateMenuItem->description,
            'category' => $updateMenuItem->category,
            'image' => $updateMenuItem->image,
            'is_available' => $updateMenuItem->is_available,
            'price' => $updateMenuItem->price,
        ];

        if ($menuItem->image !== null) {
            $oldImage = $menuItem->image;
            $path = $updateMenuItem->image->store('itemsPic', 'public');
            DB::afterRollBack(fn () => Storage::disk('public')->delete($path));
            $updatedData['image'] = $path;
            DB::afterCommit(fn () => $oldImage ? Storage::disk('public')->delete($oldImage) : null);
        }
        $menuItem->update($updatedData);
        //
        //            $menuItem->update([
        //                'name' => $updateMenuItem->name,
        //                'description' => $updateMenuItem->description,
        //                'category' => $updateMenuItem->category,
        //                'image' => $path,
        //                'is_available' => $updateMenuItem->is_available,
        //                'price' => $updateMenuItem->price,
        //            ]);
        //            DB::afterCommit(function () use ($oldImage) {
        //                if ($oldImage) {
        //                    Storage::disk('public')->delete($oldImage);
        //                }
        //            });
        //        });
    }
}
