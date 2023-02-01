<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\Resources\Json\JsonResource;

class CityController extends Controller
{
    public function index(): JsonResource
    {
        return CityResource::collection(City::query()->get());
    }
}
