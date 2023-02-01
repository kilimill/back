<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Services\RoomService;
use Illuminate\Http\JsonResponse;

class MealsController extends Controller
{
    public function index(): JsonResponse
    {
        $meals = RoomService::create()->getMeals();

        return response()->json([
            'data' => $meals,
        ]);
    }
}
