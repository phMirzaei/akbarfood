<?php

namespace App\Services;

use App\Models\Restaurant\Restaurant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RegisterRestaurantService
{
    public function execute(array $data, int $ownerId)
    {
        DB::transaction(function () use ($data, $ownerId) {
            $path = $data['permit_scan']->store('permits', 'public');
            DB::afterRollBack(fn()=>Storage::disk('public')->delete($path));
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
