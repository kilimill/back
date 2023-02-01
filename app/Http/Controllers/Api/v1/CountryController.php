<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryController extends Controller
{
    public function index(): JsonResource
    {
        return CountryResource::collection(Country::query()->get());
    }
}
