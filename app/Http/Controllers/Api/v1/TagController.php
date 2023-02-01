<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Resources\Json\JsonResource;

class TagController extends Controller
{
    public function index(): JsonResource
    {
        return TagResource::collection(Tag::query()->get());
    }
}
