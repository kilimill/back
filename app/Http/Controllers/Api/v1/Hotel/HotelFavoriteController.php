<?php

namespace App\Http\Controllers\Api\v1\Hotel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\PaginatedRequest;
use App\Http\Resources\HotelCardResource;
use App\Http\Services\HotelService;
use App\Models\Hotel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelFavoriteController extends Controller
{
    public function index(PaginatedRequest $paginatedRequest): JsonResource
    {
        $hotelService = HotelService::create();
        $hotels = $hotelService->getFavoriteHotels($paginatedRequest);
        $nextPage = $hotelService->nextPage($hotels);

        return HotelCardResource::collection($hotels)->additional(['next_page' => $nextPage]);
    }

    public function store(Hotel $hotel): JsonResponse
    {
        auth_user_or_fail()->favoriteHotels()->syncWithoutDetaching($hotel);

        return response()->json([
            'message' => 'Отель успешно добавлен в избранное.',
        ]);
    }

    public function remove(Hotel $hotel): JsonResponse
    {
        auth_user_or_fail()->favoriteHotels()->detach($hotel);

        return response()->json([
            'message' => 'Отель успешно удален из избранного.',
        ]);
    }
}
