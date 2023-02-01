<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LakeResource;
use App\Models\Lake;
use Illuminate\Http\Resources\Json\JsonResource;

class LakeController extends Controller
{
    public function index(): JsonResource
    {
        return LakeResource::collection(Lake::query()->get());
    }
}
