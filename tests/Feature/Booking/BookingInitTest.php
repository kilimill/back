<?php

namespace Tests\Feature\Booking;

use App\Http\Services\MediaService;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Media;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class BookingInitTest extends TestCase
{
    use DatabaseMigrations;

    private User $user;
    public Hotel $hotel;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->asClient()->create();
        $this->userLogin($this->user);
        $this->hotel = Hotel::factory()->create(['min_days' => 2]);
        Room::factory()->for($this->hotel)->create(['guest_count' => 5]);
        $roomsWithGroup = Room::factory()->for($this->hotel)->create(['guest_count' => 5]);
        Room::factory(2)->for($this->hotel)
            ->create(['group_id' => $roomsWithGroup->getKey(), 'guest_count' => 5,]);
    }

    public function testInitBooking(): void
    {
        $requestData = [
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
            'adult_count' => 4,
            'child_count' => 2,
            'rooms' => $this->hotel->rooms->pluck('id')->toArray(),
        ];
        $this->assertCount(0, Booking::all());

        $response = $this->postJson(route('api.bookings.init', ['hotel' => $this->hotel]), $requestData);

        $this->assertCount(1, Booking::all());

        /** @var Booking $booking */
        $booking = Booking::query()->where('hotel_id', $this->hotel->getKey())->first();

        $this->assertDatabaseHas((new Booking())->getTable(), [
            'id' => $booking->getKey(),
            'user_id' => $this->user->getKey(),
            'status_id' => Booking::STATUS_ID_PREPARE,
            'hotel_id' => $this->hotel->getKey(),
            'guest_name' => $this->user->name,
            'phone' => $this->user->phone,
            'email' => $this->user->email,
            'adult_count' => $requestData['adult_count'],
            'child_count' => $requestData['child_count'],
            'check_in' => $requestData['check_in'],
            'check_out' => $requestData['check_out'],
        ]);

        $this->assertCount(4, $booking->rooms);
        $this->assertDatabaseHas('booking_room', [
            'booking_id' => $booking->getKey(),
            'room_id' => $booking->rooms->first()->getKey(),
        ]);
        $this->assertDatabaseHas('booking_room', [
            'booking_id' => $booking->getKey(),
            'room_id' => $booking->rooms->last()->getKey(),
        ]);

        $response->assertExactJson([
            'data' => [
                'id' => $booking->getKey(),
                'hotel_id' => $booking->hotel->getKey(),
                'hotel' => $this->hotel->name,
                'guest_name' => $this->user->name,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
                'adult_count' => $requestData['adult_count'],
                'child_count' => $requestData['child_count'],
                'check_in' => $requestData['check_in'],
                'check_out' => $requestData['check_out'],
                'check_in_hour' => $booking->hotel->check_in_hour,
                'check_out_hour' => $booking->hotel->check_out_hour,
                'count_nights' => $booking->count_nights,
                'discount' => $booking->discount,
                'total_price' => $booking->total_price,
                'media' => $booking->hotel->getMedia('media')->map(function (Media $file) {
                    return [
                        'id' => $file->getKey(),
                        'url' => $file->getFullUrl(),
                        'is_preview' => $file->getCustomProperty('preview') ?? false,
                    ];
                })->toArray(),
                'extra' => $booking->hotel->extra(),
                'rooms' => $booking->hotel->roomsGroup($requestData['rooms'])->map(function (Room $room) {
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

        $this->userLogOut();

        $response = $this->postJson(route('api.bookings.init', ['hotel' => $this->hotel]), $requestData);

        $this->assertCount(2, Booking::all());

        /** @var Booking $booking */
        $booking = Booking::query()->where('hotel_id', $this->hotel->getKey())
            ->whereNull('user_id')
            ->first();

        $this->assertDatabaseHas((new Booking())->getTable(), [
            'id' => $booking->getKey(),
            'user_id' => null,
            'status_id' => Booking::STATUS_ID_PREPARE,
            'hotel_id' => $this->hotel->getKey(),
            'guest_name' => null,
            'phone' => null,
            'email' => null,
            'adult_count' => $requestData['adult_count'],
            'child_count' => $requestData['child_count'],
            'check_in' => $requestData['check_in'],
            'check_out' => $requestData['check_out'],
        ]);

        $response->assertExactJson([
            'data' => [
                'id' => $booking->getKey(),
                'hotel_id' => $booking->hotel->getKey(),
                'hotel' => $this->hotel->name,
                'guest_name' => null,
                'phone' => null,
                'email' => null,
                'adult_count' => $requestData['adult_count'],
                'child_count' => $requestData['child_count'],
                'check_in' => $requestData['check_in'],
                'check_out' => $requestData['check_out'],
                'check_in_hour' => $booking->hotel->check_in_hour,
                'check_out_hour' => $booking->hotel->check_out_hour,
                'count_nights' => $booking->count_nights,
                'discount' => $booking->discount,
                'total_price' => $booking->total_price,
                'media' => $booking->hotel->getMedia('media')->map(function (Media $file) {
                    return [
                        'id' => $file->getKey(),
                        'url' => $file->getFullUrl(),
                        'is_preview' => $file->getCustomProperty('preview') ?? false,
                    ];
                })->toArray(),
                'extra' => $booking->hotel->extra(),
                'rooms' => $booking->hotel->roomsGroup($requestData['rooms'])->map(function (Room $room) {
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

    /**
     * @dataProvider dataBookingInitValidations
     */
    public function testBookingInitValidations(int $code, array $data, array $errors): void
    {
        $response = $this->postJson(route('api.bookings.init', ['hotel' => $this->hotel]), $data);

        $response->assertStatus($code)->assertJsonFragment([
            'errors' => $errors,
        ]);
    }

    public function dataBookingInitValidations(): array
    {
        return [
            'required' => [
                422,
                [
                    'check_in' => '',
                    'check_out' => '',
                    'adult_count' => '',
                    'rooms' => '',
                ],
                [
                    "check_in" => [
                        "Поле Дата заезда обязательно для заполнения."
                    ],
                    "check_out" => [
                        "Поле Дата выезда обязательно для заполнения."
                    ],
                    "adult_count" => [
                        "Поле Взрослые обязательно для заполнения."
                    ],
                    "rooms" => [
                        "Поле Номера обязательно для заполнения."
                    ]
                ],
            ],
            'invalid_type' => [
                422,
                [
                    'check_in' => 'string',
                    'check_out' => 'string',
                    'adult_count' => 'string',
                    'child_count' => 'string',
                    'rooms' => 'string',
                    'discount' => 'string',
                ],
                [
                    "check_in" => [
                        "Значение поля Дата заезда не является датой.",
                        "Значение поля Дата заезда должно быть датой после или равной today."
                    ],
                    "check_out" => [
                        "Значение поля Дата выезда не является датой.",
                        "Значение поля Дата выезда должно быть датой после Дата заезда.",
                        "Значение поля Дата выезда должно быть датой после today."
                    ],
                    "adult_count" => [
                        "Значение поля Взрослые должно быть целым числом."
                    ],
                    "child_count" => [
                        "Значение поля Дети должно быть целым числом."
                    ],
                    "rooms" => [
                        "Значение поля Номера должно быть массивом."
                    ],
                    "discount" => [
                        "Значение поля Скидка должно быть целым числом."
                    ]
                ],
            ],

            'invalid_dates_and_adult_count_min1_and_rooms_type' => [
                422,
                [
                    'check_in' => now()->subDay()->toDateString(),
                    'check_out' => now()->toDateString(),
                    'adult_count' => 0,
                    'rooms' => ['too'],
                ],
                [
                    "check_in" => [
                        "Значение поля Дата заезда должно быть датой после или равной today."
                    ],
                    "check_out" => [
                        "Значение поля Дата выезда должно быть датой после today."
                    ],
                    "adult_count" => [
                        "Значение поля Взрослые должно быть не меньше 1."
                    ],
                    "rooms.0" => [
                        "Значение поля rooms.0 должно быть целым числом."
                    ]
                ],
            ],
            'max_count_and_exist_rooms' => [
                422,
                [
                    'check_in' => now()->toDateString(),
                    'check_out' => now()->addDays(2)->toDateString(),
                    'adult_count' => 31,
                    'child_count' => 31,
                    'rooms' => [25, 26],
                ],
                [
                    "adult_count" => [
                        "Значение поля Взрослые не может быть больше 30."
                    ],
                    "child_count" => [
                        "Значение поля Дети не может быть больше 30."
                    ],
                    "rooms.0" => [
                        "Выбранное значение для rooms.0 некорректно."
                    ],
                    "rooms.1" => [
                        "Выбранное значение для rooms.1 некорректно."
                    ]
                ],
            ],
        ];
    }

    public function testRulesPassedValidations(): void
    {
        $data = [
            'min_days' =>
                [
                    'check_in' => now()->toDateString(),
                    'check_out' => now()->addDay()->toDateString(),
                    'adult_count' => 5,
                    'child_count' => 0,
                    'rooms' => $this->hotel->rooms->pluck('id')->toArray(),
                ],
            'max_guest_count' =>
                [
                    'check_in' => now()->toDateString(),
                    'check_out' => now()->addDays(2)->toDateString(),
                    'adult_count' => 20,
                    'child_count' => 5,
                    'rooms' => $this->hotel->rooms->pluck('id')->toArray(),
                ],
        ];

        $response = $this->postJson(route('api.bookings.init', ['hotel' => $this->hotel]), $data['min_days']);
        $response->assertStatus(422)->assertJsonFragment([
            'errors' => [
                'min_days' => [
                    'Минимальный срок бронирования ' . $this->hotel->min_days . '.'
                ],
            ],
        ]);

        $guestCount = Room::query()->find($data['max_guest_count']['rooms'])->sum('guest_count');
        $response = $this->postJson(route('api.bookings.init', ['hotel' => $this->hotel]), $data['max_guest_count']);
        $response->assertStatus(422)->assertJsonFragment([
            'errors' => [
                'adult_count' => ['Общая вместимость выбранных номеров ' . $guestCount . '.'],
                'child_count' => ['Общая вместимость выбранных номеров ' . $guestCount . '.'],
            ],
        ]);
    }
}
