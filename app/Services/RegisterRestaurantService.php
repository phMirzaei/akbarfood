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
            DB::afterRollBack(fn()=>Storage::disk('public')->delete($dto->permit_scan));
            $restaurant = Restaurant::create([
                'name' => $dto->name,
                'permit_scan' => $dto->permit_scan,
                'landline_number' => $dto->landline_number,
                'city' => $dto->city,
                'street' => $dto->street,
                'alley' => $dto->alley,
                'vendor_type' => $dto->vendor_type,
                'status' => 'pending',
            ]);
            $restaurant->users()->attach($ownerId, [
                'role' => 'owner'
            ]);
        });
    }
}
