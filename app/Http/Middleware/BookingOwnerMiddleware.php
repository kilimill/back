<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiOwnerBookingException;
use App\Models\Booking;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookingOwnerMiddleware
{
    /**
     * @throws ApiOwnerBookingException
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {
        /** @var Booking $booking */
        $booking = $request->route()->parameter('booking');

        $ownerHotelsIds = auth_user_or_fail()->hotels->pluck('id')->toArray();
        if ($booking && !in_array($booking->hotel_id, $ownerHotelsIds)) {
            throw new ApiOwnerBookingException();
        }

        return $next($request);
    }
}
