<?php

namespace App\Http\Controllers\Api\v1\Hotel;

use App\Exceptions\ApiHotelValidationException;
use App\Exceptions\ApiLogicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Hotel\HotelUpsertRequest;
use App\Http\Requests\Api\v1\PaginatedRequest;
use App\Http\Resources\HotelOwnerCardResource;
use App\Http\Resources\HotelOwnerShowResource;
use App\Http\Services\HotelOwnerService;
use App\Http\Services\HotelService;
use App\Models\Hotel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;

class HotelOwnerController extends Controller
{
    public function index(PaginatedRequest $paginatedRequest): JsonResource
    {
        $hotelService = HotelService::create();
        $hotels = $hotelService->getOwnerHotels($paginatedRequest);
        $nextPage = $hotelService->nextPage($hotels);

        return HotelOwnerCardResource::collection($hotels)->additional(['next_page' => $nextPage]);
    }

    public function show(Hotel $hotel): JsonResponse
    {
        $exception = null;
        try {
            (new HotelUpsertRequest)->validationBeforeModeration($hotel, true);
        } catch (ValidationException $e) {
            $exception = (new ApiHotelValidationException())->fromLaravel($e);
        }

        return response()->json([
            'data' => HotelOwnerShowResource::make($hotel),
            'valid' => $exception ? $exception->getValidKeys() : collect(ApiHotelValidationException::STEPS)->keys(),
        ]);
    }

    /**
     * @throws ApiLogicException|ApiHotelValidationException
     */
    public function upsert(HotelUpsertRequest $hotelUpsertRequest, ?Hotel $hotel): JsonResponse
    {
        $hotelUpsertRequest->prepareRequestBeforeValidation();

        $hotelOwnerService = HotelOwnerService::create();
        $hotelOwnerService->validationBeforeUpsert($hotelUpsertRequest);
        $hotel = $hotelOwnerService->handle($hotel, $hotelUpsertRequest);
        $exception = $hotelOwnerService->validationBeforeModeration($hotel, $hotelUpsertRequest);

        return response()->json([
            'message' => 'Данные успешно сохранены.',
            'data' => HotelOwnerShowResource::make($hotel),
            'valid' => $exception ? $exception->getValidKeys() : collect(ApiHotelValidationException::STEPS)->keys(),
        ]);
    }
}
