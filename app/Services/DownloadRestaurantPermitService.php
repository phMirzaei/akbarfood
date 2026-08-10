<?php

namespace App\Services;

use App\DTOs\DownloadRestaurantPermit;
use Illuminate\Support\Facades\Storage;

class DownloadRestaurantPermitService
{
    public function execute(DownloadRestaurantPermit $downloadRestaurantPermit)
    {
        return Storage::disk('local')->download($downloadRestaurantPermit->restaurant->permit_scan);
}
}
