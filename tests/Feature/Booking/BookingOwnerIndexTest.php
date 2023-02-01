<?php

namespace Tests\Feature\Booking;

use App\Http\Services\MediaService;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class BookingOwnerIndexTest extends TestCase
{
    use DatabaseMigrations;

    private User $user;
    public Hotel $hotel;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->asOwner()->create();
        $this->userLogin($this->user);
        $this->hotel = Hotel::factory()->for($this->user)->create();
        Booking::factory(10)->for($this->hotel)->create();
    }

    public function testBookingOwnerIndexShowsFirstPage(): void
    {
        $page = 1;
        $prePage = 9;

        $hotels = $this->user
            ->hotels()
            ->with(['bookings.user','bookings.hotel','bookings.rooms'])
            ->get();

        $bookings = $hotels->map(function ($hotel){
            return $hotel->bookings;
        })
            ->first()
            ->skip(($page - 1) * $prePage)
            ->take($prePage);

        $response = $this->postJson(route('api.owner.bookings.index'), [
            'page' => $page,
            'per_page' => $prePage,
        ]);

        $response->assertOk();
        $response->assertJsonCount(9, 'data');

        $bookingsResponse = collect();
        $bookings->each(function (Booking $booking) use ($bookingsResponse) {
            $bookingsResponse->add([
                'id' => $booking->getKey(),
                'hotel_id' => $booking->hotel->getKey(),
                'user_name' => $booking->user->name,
                'status' => $booking->status_id,
                'hotel' => $booking->hotel->name,
                'guest_count' => $booking->adult_count + $booking->child_count,
                'check_in' => $booking->check_in->toDateString(),
                'check_out' => $booking->check_out->toDateString(),
                'count_nights' => $booking->count_nights,
                'total_price' => $booking->total_price,
                'preview' => MediaService::create()->getPreview($booking->hotel),

                'rooms' => $booking->hotel->roomsGroup($booking->rooms->pluck('id')->toArray())->map(function (Room $room) {
                    return [
                        'name' => $room->name,
                    ];
                }),
            ]);
        });

        $response->assertExactJson([
            'data' => $bookingsResponse->toArray(),
            'next_page' => 2,
        ]);
    }

    public function testBookingOwnerIndexShowsSecondPage(): void
    {
        $page = 2;
        $prePage = 9;

        $hotels = $this->user
            ->hotels()
            ->with(['bookings.user','bookings.hotel','bookings.rooms'])
            ->get();

        $bookings = $hotels->map(function ($hotel){
            return $hotel->bookings;
        })
            ->first()
            ->skip(($page - 1) * $prePage)
            ->take($prePage);

        $response = $this->postJson(route('api.owner.bookings.index'), [
            'page' => $page,
            'per_page' => $prePage,
        ]);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        $bookingsResponse = collect();
        $bookings->each(function (Booking $booking) use ($bookingsResponse) {
            $bookingsResponse->add([
                'id' => $booking->getKey(),
                'hotel_id' => $booking->hotel->getKey(),
                'user_name' => $booking->user->name,
                'status' => $booking->status_id,
                'hotel' => $booking->hotel->name,
                'guest_count' => $booking->adult_count + $booking->child_count,
                'check_in' => $booking->check_in->toDateString(),
                'check_out' => $booking->check_out->toDateString(),
                'count_nights' => $booking->count_nights,
                'total_price' => $booking->total_price,
                'preview' => MediaService::create()->getPreview($booking->hotel),

                'rooms' => $booking->hotel->roomsGroup($booking->rooms->pluck('id')->toArray())->map(function (Room $room) {
                    return [
                        'name' => $room->name,
                    ];
                }),
            ]);
        });

        $response->assertExactJson([
            'data' => $bookingsResponse->toArray(),
            'next_page' => null,
        ]);
    }

    public function testBookingIndexNotAuth(): void
    {
        $this->userLogOut();
        $this->postJson(route('api.owner.bookings.index'))
            ->assertUnauthorized()
            ->assertExactJson(['message' =>  'Запрос не авторизован.']);
    }

    public function testBookingIndexHasEmptyResponseWhenNoBookings(): void
    {
        $this->userLogOut();
        $notOwner = User::factory()->create();
        $this->userLogin($notOwner);
        $response = $this->postJson(route('api.owner.bookings.index'))->assertOk();

        $response->assertExactJson([
            'data' => [],
            'next_page' => null,
        ]);
    }
}
