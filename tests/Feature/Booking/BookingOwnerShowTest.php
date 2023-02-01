<?php

namespace Tests\Feature\Booking;

use App\Http\Services\MediaService;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Media;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class BookingOwnerShowTest extends TestCase
{
    use DatabaseMigrations;

    public Hotel $hotel;
    public Booking $booking;

    public function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->asOwner()->create();
        $this->userLogin($user);
        $this->hotel = Hotel::factory()->for($user)->create();
        $this->booking = Booking::factory()->for($this->hotel)->create();
    }

    public function testBookingShowReturnsCorrectResponse(): void
    {
        $response = $this->getJson(route('api.owner.bookings.show', $this->booking));

        $response->assertExactJson([
            'data' => [
                'id' => $this->booking->getKey(),
                'hotel_id' => $this->booking->hotel->getKey(),
                'user_name' => $this->booking->user->name,
                'status' => $this->booking->status_id,
                'hotel' => $this->booking->hotel->name,
                'guest_name' => $this->booking->guest_name,
                'phone' => $this->booking->phone,
                'email' => $this->booking->email,
                'comment' => $this->booking->comment,
                'adult_count' => $this->booking->adult_count,
                'child_count' => $this->booking->child_count,
                'check_in' => $this->booking->check_in->toDateString(),
                'check_out' => $this->booking->check_out->toDateString(),
                'check_in_hour' => $this->booking->hotel->check_in_hour,
                'check_out_hour' => $this->booking->hotel->check_out_hour,
                'count_nights' => $this->booking->count_nights,
                'discount' => $this->booking->discount,
                'total_price' => $this->booking->total_price,
                'media' => $this->booking->hotel->getMedia('media')->map(function (Media $file) {
                    return [
                        'id' => $file->getKey(),
                        'url' => $file->getFullUrl(),
                        'is_preview' => $file->getCustomProperty('preview') ?? false,
                    ];
                })->toArray(),
                'extra' => $this->booking->hotel->extra(),
                'rooms' => $this->booking->hotel->roomsGroup($this->booking->rooms->pluck('id')->toArray())->map(function (Room $room) {
                    return [
                        'name' => $room->name,
                        'group_id' => $room->group_id,
                        'guest_count' => $room->guest_count,
                        'meals_id' => $room->meals_id,
                        'price' => $room->price,
                        'quantity' => $room['quantity'],
                        'preview' => MediaService::create()->getPreview($room),
                    ];
                })->toArray(),
            ],
        ]);
    }

    public function testBookingShowDoesNotOwner(): void
    {
        $notOwner = User::factory()->create();
        $response = $this->actingAs($notOwner)->getJson(route('api.owner.bookings.show', $this->booking));
        $response->assertNotFound();
    }
}
