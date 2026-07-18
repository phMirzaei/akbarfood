<?php

namespace App\Services;

use App\Exceptions\ModelNotFoundException;
use App\Models\Menu\Menu;
use App\Models\Restaurant\Restaurant;

class RemoveMenuItemService
{
    public function execute(Menu $menuItem,Restaurant $restaurant)
    {
        if($menuItem->restaurant_id != $restaurant->id){
            throw new ModelNotFoundException();
        }
        $menuItem->delete();
    }
}
