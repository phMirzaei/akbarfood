<?php

namespace App\Services;

use App\Models\Restaurant\Restaurant;
use Illuminate\Support\Facades\DB;

class RegisterRestaurantService
{
    public function execute(array $data, int $ownerId)
    {
        $path = $data['permit_scan']->store('permits', 'public');
        $data['permit_scan'] = $path;
        DB::transaction(function () use ($data, $ownerId, $path) {
            $restaurant = Restaurant::create([
                'name' => $data['name'],
                'permit_scan' => $path,
                'landline_number' => $data['landline_number'],
                'city' => $data['city'],
                'street' => $data['street'],
                'alley' => $data['alley'],
                'status' => 'pending',
            ]);
            $restaurant->users()->attach($ownerId, [
                'role' => 'owner'
            ]);
        });
    }
}
