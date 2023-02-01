<?php

namespace Tests\Feature\Room;

use App\Http\Services\MediaService;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class RoomGetHotelAvailableRoomsTest extends TestCase
{
    use DatabaseMigrations;

    private Hotel $hotel;

    public function setUp(): void
    {
        parent::setUp();

        $owner = User::factory()->create();
        $this->hotel = Hotel::factory()->for($owner)->create();
        $this->userLogin($owner);
    }

    public function dataInvalidRequest(): array
    {
        return [
            'check_in_too_early' => [
                ['check_in' => Carbon::now()->subDay()->toDateString()],
                ['check_in',],
            ],
            'check_out_too_late' => [
                ['check_out' => Carbon::now()->toDateString()],
                ['check_out',],
            ],
            'invalid_dates' => [
                ['check_in' => 568767, 'check_out' => 865442,],
                ['check_in', 'check_out',],
            ],
            'invalid_adult_count' => [
                ['adult_count' => 'foo',],
                ['adult_count',],
            ],
            'adult_count_is_zero' => [
                ['adult_count' => 0,],
                ['adult_count',],
            ],
            'adult_count_more_30' => [
                ['adult_count' => 31,],
                ['adult_count',],
            ],
            'child_count_more_30' => [
                ['child_count' => 31,],
                ['child_count',],
            ],
            'counts_not_numeric' => [
                ['adult_count' => 'foo', 'child_count' => 'bar'],
                ['adult_count', 'child_count',]
            ],
        ];
    }

    /**
     * @dataProvider dataInvalidRequest
     */
    public function testValidateAvailableRooms(array $request): void
    {
        $this->postJson(
            route('api.hotels.rooms.getHotelAvailableRooms', ['hotelId' => $this->hotel->getKey()]),
            $request,
        )->assertUnprocessable();
    }

    public function testTranslateAvailableRooms(): void
    {
        $requestData = [
            'check_in' => 'foo',
            'check_out' => 'bar',
            'adult_count' => 31,
            'child_count' => 31,
        ];

        $this->postJson(
            route('api.hotels.rooms.getHotelAvailableRooms', ['hotelId' => $this->hotel->getKey()]),
            $requestData,
        )->assertJsonFragment([
            'errors' => [
                'check_in' => [
                    'Значение поля Дата заезда не является датой.',
                    'Значение поля Дата заезда должно быть датой после или равной today.'
                ],
                'check_out' => [
                    'Значение поля Дата выезда не является датой.',
                    'Значение поля Дата выезда должно быть датой после Дата заезда.',
                    'Значение поля Дата выезда должно быть датой после today.'
                ],
                'adult_count' => [
                    'Значение поля Количество взрослых не может быть больше 30.'
                ],
                'child_count' => [
                    'Значение поля Количество детей не может быть больше 30.'
                ]
            ],
        ]);
    }

    public function testAvailableRooms(): void
    {
        $requestData = [
            'check_in' => Carbon::today()->toDateString(),
            'check_out' => Carbon::tomorrow()->toDateString(),
            'adult_count' => 2,
            'child_count' => 2,
        ];
        $roomsData = collect([
            [
                'name' => 'first',
                'description' => 'some text',
                'guest_count' => 5,
                'meals_id' => 2,
                'price' => 5000,
                'price_weekend' => 6000,
                'quantity' => 3,
            ],
            [
                'name' => 'second',
                'description' => 'some text',
                'guest_count' => 2,
                'meals_id' => 2,
                'price' => 5000,
                'price_weekend' => 6000,
                'quantity' => 1,
            ],
        ]);

        $rooms = collect();

        $roomsData->each(function (array $roomData) use ($rooms) {
            $quantity = $roomData['quantity'];
            unset($roomData['quantity']);

            /** @var Room $room */
            $room = Room::factory()->for($this->hotel)->create($roomData);
            $rooms->add($room);
            if ($quantity > 1) {
                $roomData['group_id'] = $room->getKey();
                for ($i = 1; $i < $quantity; $i++) {
                    Room::factory()->for($this->hotel)->create($roomData);
                }
            }
        });

        $available_ids = $this->hotel->rooms()->where('name', $roomsData[0]['name'])->pluck('id');
        $request = $this->postJson(route('api.hotels.rooms.getHotelAvailableRooms', ['hotelId' => $this->hotel]), $requestData);
        $request->assertOk();
        /** @var Room $firstRoom */
        $firstRoom = $rooms->first();
        $request->assertExactJson([
            'data' => [[
                'name' => $roomsData[0]['name'],
                'group_id' => $firstRoom->getKey(),
                'meals_id' => $roomsData[0]['meals_id'],
                'guest_count' => $roomsData[0]['guest_count'],
                'preview' => MediaService::create()->getPreview($firstRoom),
                'price' => $roomsData[0]['price'],
                'available_ids' => $available_ids->toArray(),
            ]],
        ]);
    }

    public function testAvailableRoomsEmpty(): void
    {
        $requestData = [
            'check_in' => Carbon::today()->toDateString(),
            'check_out' => Carbon::tomorrow()->toDateString(),
            'adult_count' => 10,
            'child_count' => 10,
        ];
        $roomsData = [
            [
                'name' => 'first',
                'description' => 'some text',
                'guest_count' => 1,
                'meals_id' => 2,
                'price' => 5000,
                'price_weekend' => 6000,
            ],
            [
                'name' => 'second',
                'description' => 'some text',
                'guest_count' => 1,
                'meals_id' => 2,
                'price' => 5000,
                'price_weekend' => 6000,
            ],
        ];

        foreach ($roomsData as $roomData) {
            Room::factory()->for($this->hotel)->create($roomData);
        }
        $this->postJson(
            route('api.hotels.rooms.getHotelAvailableRooms', ['hotelId' => $this->hotel]),
            $requestData,
        )->assertOk()->assertExactJson([
            'data' => [],
        ]);
    }
}
