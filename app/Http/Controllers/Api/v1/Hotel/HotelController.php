<?php

namespace App\Http\Controllers\Api\v1\Hotel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Hotel\HotelRequest;
use App\Http\Requests\Api\v1\Hotel\HotelSearchRequest;
use App\Http\Resources\HotelCardResource;
use App\Http\Resources\HotelShowResource;
use App\Http\Services\HotelService;
use App\Models\Hotel;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class HotelController extends Controller
{
    public function index(HotelRequest $hotelRequest): JsonResource
    {
        $hotelService = HotelService::create();
        $hotelsBuilder = $hotelService->getHotelsBuilder($hotelRequest);

        if ($hotelsBuilder->doesntExist()) {
            $hotelRequest->merge(['location' => config('nollo.default_search_location')]);
            $hotelsBuilder = $hotelService->getHotelsBuilder($hotelRequest);
        }

        $hotels = $hotelsBuilder->get();
        $nextPage = $hotelService->nextPage($hotels);

        return HotelCardResource::collection($hotels)->additional([
            'next_page' => $nextPage,
            'count' => Hotel::query()->count(),
        ]);
    }

    public function search(HotelSearchRequest $hotelSearchRequest): JsonResource
    {
        $hotelService = HotelService::create();
        $hotels = $hotelService->searchHotels($hotelSearchRequest);
        $nextPage = $hotelService->nextPage($hotels);

        return HotelCardResource::collection($hotels)->additional([
            'next_page' => $nextPage,
            'count' => Hotel::query()->count(),
        ]);
    }

    public function show(Hotel $hotel): JsonResource
    {
        return HotelShowResource::make($hotel);
    }

    public function all(): JsonResource
    {
        return Cache::rememberForever('all_hotels', function () {
            $hotelService = HotelService::create();
            $hotels = $hotelService->getAllHotels();
            return HotelCardResource::collection($hotels);
        });
    }
}
