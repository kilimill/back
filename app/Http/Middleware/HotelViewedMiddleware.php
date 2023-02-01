<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiHotelOwnerException;
use App\Models\Hotel;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HotelViewedMiddleware
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {
        /** @var Hotel $hotel */
        $hotel = $request->route()->parameter('hotel');
        $user = auth_user_or_null();

        if ($hotel && $user && $hotel->status_id === Hotel::STATUS_ID_ACTIVE) {
            $user->viewedHotels()->attach($hotel);
        }

        return $next($request);
    }
}
