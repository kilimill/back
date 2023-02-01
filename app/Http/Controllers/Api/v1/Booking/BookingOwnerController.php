<?php

namespace App\Http\Controllers\Api\v1\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Booking\BookingRequest;
use App\Http\Requests\Api\v1\PaginatedRequest;
use App\Http\Resources\Booking\BookingCardResource;
use App\Http\Resources\Booking\BookingResource;
use App\Http\Services\BookingService;
use App\Models\Booking;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingOwnerController extends Controller
{
    public function index(PaginatedRequest $paginatedRequest): JsonResource
    {
        $bookingService = BookingService::create();
        $bookings = $bookingService->getOwnerBookings($paginatedRequest);
        $nextPage = $bookingService->nextPage($bookings);

        return BookingCardResource::collection($bookings)->additional(['next_page' => $nextPage]);
    }

    public function show(Booking $booking): JsonResource
    {
        return BookingResource::make($booking);
    }
}
