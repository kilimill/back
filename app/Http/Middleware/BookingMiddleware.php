<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiBookingException;
use App\Models\Booking;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookingMiddleware
{
    /**
     * @throws ApiBookingException
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {
        /** @var Booking $booking */
        $booking = $request->route()->parameter('booking');

        if ($booking && $booking->user_id !== auth_user_or_fail()->getKey()) {
            throw new ApiBookingException();
        }

        return $next($request);
    }
}
