<?php

namespace Tests\Feature\Room;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class RoomDeleteTest extends TestCase
{
    use DatabaseMigrations;

    private Hotel $hotel;

    public function setUp(): void
    {
        parent::setUp();

        $owner = User::factory()->create();
        $this->hotel = Hotel::factory()->for($owner)->create([
            'status_id' => Hotel::STATUS_ID_ACTIVE,
        ]);
        $this->userLogin($owner);
    }

    public function testHotelOwnerCanNotRemoveRoomForWrongHotel(): void
    {
        $room = Room::factory()->create();

        $this->deleteJson(route('api.hotels.rooms.remove', [
            'hotel' => $this->hotel,
            'room' => $room,
        ]))->assertNotFound();

        $this->assertDatabaseHas((new Room())->getTable(), [
            'id' => $room->getKey(),
        ]);
    }

    public function testOnlyHotelOwnerCanRemoveRoom()
    {
        $notOwner = User::factory()->create();

        $this->userLogin($notOwner);
        $room = Room::factory()->for($this->hotel)->create();

        $this->actingAs($notOwner)->deleteJson(route('api.hotels.rooms.remove', [
            'hotel' => $this->hotel,
            'room' => $room,
        ]))->assertNotFound();
    }

    public function testRemoveRoomByNotAuthUser()
    {
        $this->userLogOut();
        $room = Room::factory()->for($this->hotel)->create();
        $this->deleteJson(route('api.hotels.rooms.remove', [
            'hotel' => $this->hotel,
            'room' => $room,
        ]))->assertUnauthorized();
    }

    public function testRemoveRoomForNotExistedHotelInDb()
    {
        $room = Room::factory()->for($this->hotel)->create();

        $this->deleteJson(route('api.hotels.rooms.remove', [
            'hotel' => 100500,
            'room' => $room,
        ]))->assertNotFound();
    }

    public function testRemoveRoomForNotExistedRoomInDb()
    {
        $this->deleteJson(route('api.hotels.rooms.remove', [
            'hotel' => $this->hotel->getKey(),
            'room' => 100500,
        ]))->assertNotFound();
    }

    public function testHotelOwnerCanForceDeleteRoomWithOutBookings(): void
    {
        $room = Room::factory()->for($this->hotel)->create();
        $roomArray = $room->toArray();
        unset($roomArray['id']);

        Room::factory(2)->create($roomArray);

        $this->assertCount(3, Room::all());
        $this->assertDatabaseHas((new Room())->getTable(), [
            'group_id' => $room->getKey(),
        ]);

        $this->assertDatabaseHas((new Hotel())->getTable(), [
            'id' => $this->hotel->getKey(),
            'status_id' => Hotel::STATUS_ID_ACTIVE,
        ]);

        $this->deleteJson(route('api.hotels.rooms.remove', [
            'hotel' => $this->hotel,
            'room' => $room,
        ]))->assertOk()->assertExactJson([
            'message' => 'Номер успешно удален.',
        ]);

        $this->assertCount(0, Room::all());
        $this->assertDatabaseMissing((new Room())->getTable(), [
            'group_id' => $room->getKey(),
        ]);

        $this->assertDatabaseHas((new Hotel())->getTable(), [
            'id' => $this->hotel->getKey(),
            'status_id' => Hotel::STATUS_ID_UNDER_REVIEW,
        ]);
    }

    public function testHotelOwnerCanSoftDeleteRoomWithActiveBooking(): void
    {
        /** @var Room $room */
        $room = Room::factory()
            ->for($this->hotel)
            ->has(
                $bookingFactory = Booking::factory()->withCheckIn(now()->subDays(5))->withCheckOut(now()->addDays(5))
            )->create();

        $roomArray = $room->toArray();
        unset($roomArray['id']);
        Room::factory(2)->has($bookingFactory)->create($roomArray);

        Room::all()->each(function (Room $room) {
            $this->assertNull($room->deleted_at);
        });

        $this->deleteJson(route('api.hotels.rooms.remove', [
            'hotel' => $this->hotel,
            'room' => $room,
        ]))->assertOk()->assertExactJson([
            'message' => 'Номер успешно удален. Номер имеет активные бронирования, которые должны быть выполнены.',
        ]);

        Room::all()->each(function (Room $room) {
            $this->assertNotNull($room->deleted_at);
        });

        $this->assertDatabaseHas((new Room())->getTable(), [
            'group_id' => $room->getKey(),
        ]);
    }

    public function testHotelOwnerCanSoftDeleteRoomWithFutureBookings(): void
    {
        /** @var Room $room */
        $room = Room::factory()
            ->for($this->hotel)
            ->has(
                $bookingFactory = Booking::factory()->withCheckIn(now()->addDays(5))->withCheckOut(now()->addDays(10))
            )->create();

        $roomArray = $room->toArray();
        unset($roomArray['id']);
        Room::factory(2)->has($bookingFactory)->create($roomArray);

        Room::all()->each(function (Room $room) {
            $this->assertNull($room->deleted_at);
        });

        $this->deleteJson(route('api.hotels.rooms.remove', [
            'hotel' => $this->hotel,
            'room' => $room,
        ]))->assertOk()->assertExactJson([
            'message' => 'Номер успешно удален. Номер имеет активные бронирования, которые должны быть выполнены.',
        ]);

        Room::all()->each(function (Room $room) {
            $this->assertNotNull($room->deleted_at);
        });

        $this->assertDatabaseHas((new Room())->getTable(), [
            'group_id' => $room->getKey(),
        ]);
    }
}
