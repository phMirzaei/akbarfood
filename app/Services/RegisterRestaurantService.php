<?php

namespace App\Services;

use App\DTOs\RegisterRestaurant;
use App\Models\Restaurant\Restaurant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RegisterRestaurantService
{
    public function execute(RegisterRestaurant $registerRestaurant, int $ownerId)
    {
        DB::transaction(function () use ($registerRestaurant, $ownerId) {
            DB::afterRollBack(fn()=>Storage::disk('public')->delete($registerRestaurant->permit_scan));
            $restaurant = Restaurant::create([
                'name' => $registerRestaurant->name,
                'permit_scan' => $registerRestaurant->permit_scan,
                'landline_number' => $registerRestaurant->landline_number,
                'city' => $registerRestaurant->city,
                'street' => $registerRestaurant->street,
                'alley' => $registerRestaurant->alley,
                'status' => 'pending',
            ]);
            $restaurant->users()->attach($ownerId, [
                'role' => 'owner'
            ]);
        });
    }
}
