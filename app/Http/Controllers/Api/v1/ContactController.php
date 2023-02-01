<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Hotel;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function remove(Hotel $hotel, Contact $contact): JsonResponse
    {
        Contact::query()
            ->where('hotel_id', $hotel->getKey())
            ->where('id', $contact->getKey())
            ->delete();

        $hotel->status_id = Hotel::STATUS_ID_UNDER_REVIEW;
        $hotel->save();

        return response()->json([
            'message' => 'Контакт успешно удален',
        ]);
    }
}
