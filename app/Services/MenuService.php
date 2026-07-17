<?php

namespace App\Services;

use App\DTOs\AddMenuItem;
use App\Exceptions\RestaurantNotApprovedException;
use App\Models\Menu\Menu;
use App\Models\Restaurant\Restaurant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use mysql_xdevapi\Exception;

class MenuService
{
    public function addItems(AddMenuItem $addMenuItem,Restaurant $restaurant)
    {
        if ($restaurant->status !== 'approved') {
            throw new RestaurantNotApprovedException();
        }
            DB::transaction(function () use ($addMenuItem) {
                DB::afterRollBack(fn() => Storage::disk('public')->delete($addMenuItem->image));
                Menu::create([
                    'name' => $addMenuItem->name,
                    'description' => $addMenuItem->description,
                    'category' => $addMenuItem->category,
                    'image' => $addMenuItem->image,
                    'is_available' => $addMenuItem->is_available,
                    'price' => $addMenuItem->price,
                ]);
            });
        }
    }
