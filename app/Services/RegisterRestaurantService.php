<?php

namespace App\Services;

use App\DTOs\RegisterRestaurantDto;
use App\Models\Restaurant\Restaurant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RegisterRestaurantService
{
    public function execute(RegisterRestaurantDto $dto, int $ownerId)
    {
        DB::transaction(function () use ($dto, $ownerId) {
            $path = $dto->permit_scan->store('permits', 'public');
            DB::afterRollBack(fn()=>Storage::disk('public')->delete($path));
            $restaurant = Restaurant::create([
                'name' => $dto->name,
                'permit_scan' => $path,
                'landline_number' => $dto->landline_number,
                'city' => $dto->city,
                'street' => $dto->street,
                'alley' => $dto->alley,
                'status' => 'pending',
            ]);
            $restaurant->users()->attach($ownerId, [
                'role' => 'owner'
            ]);
        });
    }
}
