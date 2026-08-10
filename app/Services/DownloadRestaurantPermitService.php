<?php

namespace App\Services;

use App\DTOs\DownloadRestaurantPermit;
use App\Models\Restaurant\Restaurant;
use Illuminate\Support\Facades\Storage;

class DownloadRestaurantPermitService
{
    public function execute(DownloadRestaurantPermit $downloadRestaurantPermit)
    {
        $restaurant = Restaurant::findOrFail($downloadRestaurantPermit->restaurantId);

        return Storage::disk('local')->download($restaurant->permit_scan);
    }
}
