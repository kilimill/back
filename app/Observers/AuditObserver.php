<?php

namespace App\Observers;

use App\Http\Resources\HotelCardResource;
use App\Http\Services\HotelService;
use Illuminate\Support\Facades\Cache;
use OwenIt\Auditing\Models\Audit;

class AuditObserver
{
    public function created(Audit $audit): void
    {
        if ($audit->auditable_type == 'App\Models\Hotel' || $audit->auditable_type == 'App\Models\Room') {
            $hotelService = HotelService::create();
            $hotels = $hotelService->getAllHotels();
            $hotels = HotelCardResource::collection($hotels);
            Cache::put('all_hotels', $hotels);
        }
    }
}
