<?php

namespace Tests\Unit;

use App\Models\Hotel;
use App\Models\NovaUser;
use App\Notifications\NewHotelNotification;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class HotelObserverTest extends TestCase
{
    use DatabaseMigrations;

    public function testHotelObserverHotelCreated()
    {
        Notification::fake();

        $users = NovaUser::factory(2)->create();
        $hotel = Hotel::factory()->create();

        Notification::assertCount(2);
        Notification::assertSentTo($users, NewHotelNotification::class, function (NewHotelNotification $notification) use ($hotel) {
            return $notification->hotel->getKey() === $hotel->getKey();
        });
    }
}
