<?php

namespace App\Services;

use App\DTOs\RemoveMenuItem;
use App\Exceptions\MenuItemNotInRestaurantException;
use App\Exceptions\RestaurantNotApprovedException;
use App\Models\Menu\Menu;
use App\Models\Restaurant\Restaurant;

class RemoveMenuItemService
{
    public function execute(RemoveMenuItem $removeMenuItem): void
    {
        $menuItem = Menu::findOrFail($removeMenuItem->menuId);
        $restaurant = Restaurant::findOrFail($removeMenuItem->restaurantId);
        if ($menuItem->restaurant_id != $restaurant->id) {
            throw new MenuItemNotInRestaurantException;
        }
        if (! $restaurant->isApproved()) {
            throw new RestaurantNotApprovedException;
        }
        $menuItem->delete();
    }
}
