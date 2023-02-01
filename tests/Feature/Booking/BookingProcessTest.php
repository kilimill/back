<?php

namespace Tests\Feature\Booking;

use App\Http\Services\MediaService;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Media;
use App\Models\Room;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class BookingProcessTest extends TestCase
{
    use DatabaseMigrations;

    public Hotel $hotel;
    public User $user;
    public Booking $booking;
    public Collection $rooms;
    private array $processData;

    public function setUp(): void
    {
        parent::setUp();

        $this->booking = Booking::factory()->create(
            [
                'status_id' => Booking::STATUS_ID_PREPARE,
                'user_id' => null,
                'guest_name' => null,
                'phone' => null,
                'email' => null,
            ]);
    }

    public function testProcessBookingSameGuest(): void
    {
        $this->assertDatabaseHas((new Booking())->getTable(), [
            'id' => $this->booking->getKey(),
            'status_id' => Booking::STATUS_ID_PREPARE,
            'user_id' => null,
            'guest_name' => null,
            'phone' => null,
            'email' => null,
        ]);

        $user = User::factory()->asClient()->create([
            'name' => null,
            'email' => null,
        ]);
        $this->userLogin($user);

        $processData = [
            'is_another_guest' => false,
            'guest_name' => 'Guest Name',
            'phone' => $user->phone,
            'email' => 'test@test.com',
            'comment' => 'Test Comment',
        ];

        $response = $this
            ->postJson(route('api.bookings.process', ['booking' => $this->booking]), $processData)
            ->assertOk();
        $user->refresh();

        $this->assertDatabaseMissing((new Booking())->getTable(), [
            'id' => $this->booking->getKey(),
            'status_id' => Booking::STATUS_ID_PREPARE,
            'user_id' => null,
            'guest_name' => null,
            'phone' => null,
            'email' => null,
        ]);

        $this->assertDatabaseHas((new Booking())->getTable(), [
            'id' => $this->booking->getKey(),
            'status_id' => Booking::STATUS_ID_PROCESS,
            'user_id' => $user->getKey(),
            'guest_name' => $user->name,
            'phone' =>  $user->phone,
            'email' => $user->email,
        ]);

        $this->assertDatabaseHas((new User())->getTable(), [
            'id' => $user->getKey(),
            'name' => $processData['guest_name'],
            'phone' => $processData['phone'],
            'email' => $processData['email'],
        ]);

        $response->assertExactJson([
            'data' => [
                'id' => $this->booking->getKey(),
                'hotel_id' => $this->booking->hotel->getKey(),
                'user_name' => $user->name,
                'status' => Booking::STATUS_ID_PROCESS,
                'hotel' => $this->booking->hotel->name,
                'guest_name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'comment' => $processData['comment'],
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

    public function testProcessBookingAnotherGuest(): void
    {
        $this->assertDatabaseHas((new Booking())->getTable(), [
            'id' => $this->booking->getKey(),
            'status_id' => Booking::STATUS_ID_PREPARE,
            'user_id' => null,
            'guest_name' => null,
            'phone' => null,
            'email' => null,
        ]);

        $user = User::factory()->asClient()->create([
            'name' => null,
            'email' => null,
        ]);
        $this->userLogin($user);

        $processData = [
            'is_another_guest' => true,
            'guest_name' => 'Guest Name',
            'phone' => '77777777777',
            'email' => 'test@test.com',
            'comment' => 'Test Comment',
        ];

        $response = $this
            ->postJson(route('api.bookings.process', ['booking' => $this->booking]), $processData)
            ->assertOk();
        $user->refresh();

        $this->assertDatabaseMissing((new Booking())->getTable(), [
            'id' => $this->booking->getKey(),
            'status_id' => Booking::STATUS_ID_PREPARE,
            'user_id' => null,
            'guest_name' => null,
            'phone' => null,
            'email' => null,
        ]);

        $this->assertDatabaseHas((new Booking())->getTable(), [
            'id' => $this->booking->getKey(),
            'status_id' => Booking::STATUS_ID_PROCESS,
            'user_id' => $user->getKey(),
            'guest_name' => $processData['guest_name'],
            'phone' =>  $processData['phone'],
            'email' => $processData['email'],
        ]);

        $this->assertDatabaseHas((new User())->getTable(), [
            'id' => $user->getKey(),
            'name' => null,
            'phone' => $user->phone,
            'email' => null,
        ]);

        $response->assertExactJson([
            'data' => [
                'id' => $this->booking->getKey(),
                'hotel_id' => $this->booking->hotel->getKey(),
                'user_name' => $user->name,
                'status' => Booking::STATUS_ID_PROCESS,
                'hotel' => $this->booking->hotel->name,
                'guest_name' => $processData['guest_name'],
                'phone' => $processData['phone'],
                'email' => $processData['email'],
                'comment' => $processData['comment'],
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

    public function testProcessBookingSameGuestPhoneIsUnique(): void
    {
        User::factory()->asClient()->create([
            'phone' => $phone = '78888888888',
        ]);

        $user = User::factory()->asClient()->create();
        $this->userLogin($user);

        $processData = [
            'is_another_guest' => false,
            'guest_name' => 'Guest Name',
            'phone' => $phone,
            'email' => $user->email,
            'comment' => 'Test Comment',
        ];

        $response = $this
            ->postJson(route('api.bookings.process', ['booking' => $this->booking]), $processData)
            ->assertUnprocessable();

        $response->assertJsonFragment([
            'errors' => [
                'phone' => ['Такое значение поля Телефон уже существует.'],
            ],
        ]);
    }

    public function testProcessBookingSameGuestEmailIsUnique(): void
    {
        User::factory()->asClient()->create([
            'email' => $email = 'some@email.test',
        ]);

        $user = User::factory()->asClient()->create();
        $this->userLogin($user);

        $processData = [
            'is_another_guest' => false,
            'guest_name' => 'Guest Name',
            'phone' => $user->phone,
            'email' => $email,
            'comment' => 'Test Comment',
        ];

        $response = $this
            ->postJson(route('api.bookings.process', ['booking' => $this->booking]), $processData)
            ->assertUnprocessable();

        $response->assertJsonFragment([
            'errors' => [
                'email' => ['Такое значение поля Электронная почта уже существует.'],
            ],
        ]);
    }

    /**
     * @dataProvider dataBookingProcessValidations
     */
    public function testBookingProcessValidations(int $code, array $data, array $errors): void
    {
        $response = $this->postJson(route('api.bookings.process', ['booking' => $this->booking]), $data);
        $response->assertStatus($code)->assertJsonFragment([
            'errors' => $errors,
        ]);
    }

    public function dataBookingProcessValidations(): array
    {
        return [
            'required' => [
                422,
                [
                    'is_another_guest' => '',
                    'guest_name' => '',
                    'phone' => '',
                    'email' => '',
                ],
                [
                    'is_another_guest' => [
                        'Поле Гостем будет другой человек обязательно для заполнения.',
                    ],
                    'guest_name' => [
                        'Поле Имя гостя обязательно для заполнения.',
                    ],
                    'phone' => [
                        'Поле Телефон обязательно для заполнения.',
                    ],
                    'email' => [
                        'Поле Электронная почта обязательно для заполнения.',
                    ],
                ],
            ],
            'invalid_type' => [
                422,
                [
                    'is_another_guest' => 123,
                    'guest_name' => 123,
                    'phone' => 123,
                    'email' => 123,
                    'comment' => 123,
                ],
                [
                    'is_another_guest' => [
                        'Значение поля Гостем будет другой человек должно быть логического типа.',
                    ],
                    'guest_name' => [
                        'Значение поля Имя гостя должно быть строкой.',
                    ],
                    'phone' => [
                        'Поле Телефон имеет неправильный формат.',
                    ],
                    'email' => [
                        'Значение поля Электронная почта должно быть действительным электронным адресом.',
                    ],
                    'comment' => [
                        'Значение поля Комментарий должно быть строкой.',
                        'Количество символов в поле Комментарий должно быть не меньше 5.',
                    ]
                ],
            ],

            'invalid_min' => [
                422,
                [
                    'is_another_guest' => false,
                    'guest_name' => 'a',
                    'phone' => 'a',
                    'email' => 'test@test.com',
                    'comment' => 'a',
                ],
                [
                    'guest_name' => [
                        'Количество символов в поле Имя гостя должно быть не меньше 2.',
                    ],
                    'phone' => [
                        'Поле Телефон имеет неправильный формат.',
                    ],
                    'comment' => [
                        'Количество символов в поле Комментарий должно быть не меньше 5.',
                    ]
                ],
            ],
            'invalid_max' => [
                422,
                [
                    'is_another_guest' => false,
                    'guest_name' => Factory::create()->realTextBetween(31),
                    'phone' => Factory::create()->realTextBetween(21),
                    'email' => 'test@test.com',
                    'comment' => Factory::create()->realTextBetween(501, 550),
                ],
                [
                    'guest_name' => [
                        'Количество символов в поле Имя гостя не может превышать 30.',
                    ],
                    'phone' => [
                        'Поле Телефон имеет неправильный формат.',
                    ],
                    'comment' => [
                        'Количество символов в поле Комментарий не может превышать 500.',
                    ]
                ],
            ],
        ];
    }
}
