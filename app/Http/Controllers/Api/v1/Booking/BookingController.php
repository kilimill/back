<?php

namespace App\Http\Controllers\Api\v1\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Booking\BookingInitRequest;
use App\Http\Requests\Api\v1\Booking\BookingProcessRequest;
use App\Http\Requests\Api\v1\PaginatedRequest;
use App\Http\Resources\Booking\BookingCardResource;
use App\Http\Resources\Booking\BookingInitResource;
use App\Http\Resources\Booking\BookingResource;
use App\Http\Services\BookingService;
use App\Models\Booking;
use App\Models\Hotel;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingController extends Controller
{
    public function index(PaginatedRequest $paginatedRequest): JsonResource
    {
        $bookingService = BookingService::create();
        $bookings = $bookingService->getBookings($paginatedRequest);
        $nextPage = $bookingService->nextPage($bookings);

        return BookingCardResource::collection($bookings)->additional(['next_page' => $nextPage]);
    }

    public function show(Booking $booking): JsonResource
    {
        return BookingResource::make($booking);
    }

    public function init(BookingInitRequest $bookingInitRequest, Hotel $hotel): JsonResource
    {
        $booking = BookingService::create()->createInit($bookingInitRequest);

        return BookingInitResource::make($booking);
    }

    public function process(BookingProcessRequest $bookingProcessRequest, Booking $booking): JsonResource
    {
        $booking = BookingService::create()->process($bookingProcessRequest);

        return BookingResource::make($booking);
    }
}
