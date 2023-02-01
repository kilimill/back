<?php

namespace App\Http\Controllers\Api\v1\Hotel;

use App\Http\Controllers\Controller;
use App\Http\Resources\HotelCardResource;
use App\Models\Hotel;
use App\Models\HotelViewed;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelViewedController extends Controller
{
    public function index(): JsonResource
    {
        $hotelIds = HotelViewed::query()
            ->selectRaw('hotel_id, MAX(created_at) as created_at')
            ->where('user_id', auth_user_or_fail()->getKey())
            ->orderBy('created_at', 'desc')
            ->groupBy('hotel_id')
            ->limit(config('nollo.per_page'))
            ->pluck('hotel_id');

        $hotels = collect();
        if ($hotelIds->isNotEmpty()) {
            $ids = implode(',', $hotelIds->toArray());

            $hotels = Hotel::query()
                ->with('city', 'rooms')
                ->whereIn('id', $hotelIds)
                ->orderByRaw("FIELD(id, $ids)")
                ->get();
        }
        return HotelCardResource::collection($hotels);
    }
}
