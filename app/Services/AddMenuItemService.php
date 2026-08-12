<?php

namespace App\Services;

use App\DTOs\AddMenuItem;
use App\Exceptions\RestaurantNotApprovedException;
use App\Models\Menu\Menu;
use App\Models\Restaurant\Restaurant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AddMenuItemService
{
    public function execute(AddMenuItem $addMenuItem, Restaurant $restaurant)
    {
        if (! $restaurant->isApproved()) {
            throw new RestaurantNotApprovedException;
        }
        DB::transaction(function () use ($addMenuItem, $restaurant) {

            $path = $addMenuItem->image->store('itemsPic', 'public');

            DB::afterRollBack(fn () => Storage::disk('public')->delete($path));

            Menu::create([
                'name' => $addMenuItem->name,
                'description' => $addMenuItem->description,
                'category' => $addMenuItem->category,
                'image' => $path,
                'is_available' => $addMenuItem->is_available,
                'price' => $addMenuItem->price,
                'restaurant_id' => $restaurant->id,
            ]);
        });
    }
}
