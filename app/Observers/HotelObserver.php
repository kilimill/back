<?php

namespace App\Observers;

use App\Models\Hotel;
use App\Models\NovaUser;
use App\Notifications\NewHotelNotification;
use Illuminate\Support\Facades\Notification;

class HotelObserver
{
    public function created(Hotel $hotel): void
    {
        // Send notification to Nova admins when a new hotel created
        Notification::send(NovaUser::all(), new NewHotelNotification($hotel));
    }
}
