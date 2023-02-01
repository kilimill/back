<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegionResource;
use App\Models\Region;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionController extends Controller
{
    public function index(): JsonResource
    {
        return RegionResource::collection(Region::query()->get());
    }
}
