<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiHotelOwnerException;
use App\Models\Hotel;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HotelOwnerMiddleware
{
    /**
     * @throws ApiHotelOwnerException
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {
        /** @var Hotel $hotel */
        $hotel = $request->route()->parameter('hotel');

        if ($hotel && $hotel->user_id !== auth_user_or_fail()->getKey()) {
            throw new ApiHotelOwnerException();
        }

        return $next($request);
    }
}
