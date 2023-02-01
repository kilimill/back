<?php

namespace Tests\Feature\Hotel;

use App\Http\Services\MediaService;
use App\Models\Booking;
use App\Models\City;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\Region;
use App\Models\Room;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class HotelSearchTest extends TestCase
{
    use DatabaseMigrations;

    public function dataSearchRequest(): array
    {
        return [
            'поиск_по_тегам' => [1, 0, ['tags' => [1], 'location' => 'Сочи']],
            'поиск_без_критериев' => [2, 0, ['location' => 'Сочи']],
            'свободные_даты' => [2, 0,
                [
                    'check_in' => now()->addMonth()->toDateString(),
                    'check_out' => now()->addMonths(2)->toDateString(),
                    'location' => 'Сочи',
                ]
            ],
            'минимальный_срок_бронирования' => [1, 1,
                [
                    'check_in' => now()->addMonth()->toDateString(),
                    'check_out' => now()->addMonth()->addDay()->toDateString(),
                    'location' => 'Сочи',
                ]
            ],
            'даты_внутри_диапозона_бронирования' => [1, 1,
                [
                    'check_in' => now()->addDays(2)->toDateString(),
                    'check_out' => now()->addDays(3)->toDateString(),
                    'location' => 'Сочи',
                ]
            ],
            'дата_заезда_в_день_выезда_бронирования' => [1, 1,
                [
                    'check_in' => now()->addDay()->toDateString(),
                    'check_out' => now()->addWeeks(3)->toDateString(),
                    'location' => 'Сочи',
                ]
            ],
            'дата_выезда_в_день_заезда_бронирования' => [1, 0,
                [
                    'check_in' => now()->toDateString(),
                    'check_out' => now()->addDays(2)->toDateString(),
                    'location' => 'Сочи',
                ]
            ],
            'сортировка_по_полю' => [2, 1, ['sort_field' => 'distance_city', 'location' => 'Сочи',]],
            'сортировка_с_обр_направлением' => [2, 0,
                [
                    'sort_field' => 'distance_city',
                    'sort_direction' => 'desc',
                    'location' => 'Сочи',
                ]
            ],
            'поиск_по_названию_города' => [2, 0, ['location' => 'Сочи']],
            'поиск_по_названию_отеля' => [1, 0, ['location' => 'Альбатрос']],
            'поиск_по_количеству_гостей' => [1, 0, ['guest_count' => 5, 'location' => 'Сочи']],
            'поиск_по_min_цене' => [1, 1, ['min_price' => 9000, 'location' => 'Сочи']],
            'поиск_по_max_цене' => [1, 0, ['max_price' => 5000, 'location' => 'Сочи']],
            'поиск_в_диапозоне_цен' => [1, 0, ['min_price' => '5000', 'max_price' => '8000', 'location' => 'Сочи']],
            'поиск_по_питанию' => [1, 0, ['meals_id' => Room::MEALS_ID_5, 'location' => 'Сочи']],
        ];
    }

    public function dataInvalidRequest(): array
    {
        return [
            'invalid_location' => [['location' => ''], ['location']],
            'invalid_check_in_check_out' => [
                ['check_in' => now()->subDay()->toDateString(), 'check_out' => now()->toDateString()],
                ['check_in', 'check_out']],
            'invalid_check_out' => [
                ['check_in' => now()->toDateString(), 'check_out' => now()->toDateString()],
                ['check_out']],
            'invalid_dates' => [['check_in' => 568767, 'check_out' => 865442], ['check_in', 'check_out']],
            'invalid_guest_count' => [['guest_count' => 'foo'], ['guest_count']],
            'invalid_min_price' => [['min_price' => 'foo'], ['min_price']],
            'invalid_max_price' => [['max_price' => 'foo'], ['max_price']],
            'invalid_meals' => [['meals_id' => 85], ['meals_id']],
            'invalid_tags' => [['tags' => 'море'], ['tags']],
            'invalid_page' => [['page' => 'foo'], ['page']],
        ];
    }

    /**
     * @dataProvider dataSearchRequest
     */
    public function testSearchHotel(int $expectedCount, int $expectedHotel, array $requestData = []): void
    {
        $dataHotels = $this->setupDataHotels();

        $response = $this->postJson(route('api.hotels.search', $requestData));
        $response->assertOk()
            ->assertValid(array_keys($requestData))
            ->assertJsonCount($expectedCount, 'data')
            ->assertJsonPath('data.0.name', $dataHotels['hotels'][$expectedHotel]['name']);
    }

    public function testEmptySearchHotel(): void
    {
        $this->postJson(route('api.hotels.search'), ['location' => 'test location'])
            ->assertOk()
            ->assertExactJson([
                'data' => [],
                'next_page' => null,
                'count' => 0,
            ]);
    }

    public function testSearchHotelWithoutAvailableRooms(): void
    {
        /** @var Hotel $hotel */
        $hotel = Hotel::factory()->create();
        $guestCount = 2;

        Room::factory()->for($hotel)->create(['guest_count' => $guestCount]);

        $this->postJson(route('api.hotels.search'), [
            'guest_count' => $guestCount + 1,
            'location' => $hotel->city->name,
        ])->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('count', 1);
    }

    public function testSearchPaginateHotel(): void
    {
        /** @var City $city */
        $city = City::factory()->create();
        Hotel::factory(config('nollo.per_page') + 1)
            ->for($city)
            ->for($city->region)
            ->for($city->country)
            ->has(Room::factory())->create();

        $this->postJson(route('api.hotels.search'), ['location' => $city->name])
            ->assertOk()
            ->assertJsonCount(config('nollo.per_page'), 'data')
            ->assertJsonPath('next_page', 2)
            ->assertJsonPath('count', config('nollo.per_page') + 1);

        $this->postJson(route('api.hotels.search', ['page' => 2, 'location' => $city->name]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('next_page', null)
            ->assertJsonPath('count', config('nollo.per_page') + 1);

        $this->postJson(route('api.hotels.search', ['page' => 2, 'per_page' => 4, 'location' => $city->name]))
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('next_page', 3)
            ->assertJsonPath('count', config('nollo.per_page') + 1);
    }

    /**
     * @dataProvider dataInvalidRequest
     */
    public function testSearchValidateHotel(array $requestInvalid, array $expectedKeys): void
    {
        Hotel::factory(10)->has(Room::factory())->create();
        $this->postJson(route('api.hotels.search'), $requestInvalid)
            ->assertUnprocessable()
            ->assertInvalid($expectedKeys);
    }

    public function testTranslateMessageHotel(): void
    {
        /** @var City $city */
        $city = City::factory()->create();
        Hotel::factory(10)
            ->for($city)
            ->for($city->region)
            ->for($city->country)
            ->has(Room::factory())->create();

        $response = $this->postJson(route('api.hotels.search', [
            'check_in' => 568767,
            'check_out' => 865442,
            'location' => $city->name,
        ]));

        $response->assertInvalid(['check_in', 'check_out'])
            ->assertJsonFragment([
                'errors' => [
                    'check_in' => [
                        'Значение поля Дата заезда не является датой.',
                        'Значение поля Дата заезда должно быть датой после или равной today.'
                    ],
                    'check_out' => [
                        'Значение поля Дата выезда не является датой.',
                        'Значение поля Дата выезда должно быть датой после Дата заезда.',
                        'Значение поля Дата выезда должно быть датой после today.'
                    ]
                ]
            ]);
    }

    private function setupDataHotels(): array
    {
        $data = ['country' => 'Россия',
            'region' => 'Краснодарский край',
            'city' => 'Сочи',
            'tag1' => 'море',
            'tag2' => 'охота',
        ];

        $country = Country::factory()->create(['name' => $data['country']]);
        $region = Region::factory()->for($country)->create(['name' => $data['region']]);
        $city = City::factory()->for($country)->for($region)->create(['name' => $data['city']]);
        $tag1 = Tag::factory()->create(['name' => $data['tag1']]);
        $tag2 = Tag::factory()->create(['name' => $data['tag2']]);

        Hotel::factory()->existingLocation()->create(['status_id' => Hotel::STATUS_ID_DRAFT]);
        Hotel::factory(7)->for($country)->create();

        $data['hotels'] = [
            ['name' => 'Альбатрос',
                'city' => $city,
                'tag' => $tag1,
                'distance_city' => 9,
                'rooms' => [
                    [
                        'guest_count' => 5,
                        'meals_id' => Room::MEALS_ID_5,
                        'price' => 5000,
                        'is_booked' => true,
                    ],
                    [
                        'guest_count' => 5,
                        'meals_id' => Room::MEALS_ID_5,
                        'price' => 5000,
                        'is_booked' => false,
                    ],
                ],
                'check_in' => now()->addDays(2)->toDateString(),
                'check_out' => now()->addWeeks(2)->toDateString(),
                'min_days' => 2,
            ],
            ['name' => 'Ласточка',
                'city' => $city,
                'tag' => $tag2,
                'distance_city' => 5,
                'rooms' => [
                    [
                        'guest_count' => 1,
                        'meals_id' => Room::MEALS_ID_1,
                        'price' => 9000,
                        'is_booked' => true,
                    ],
                ],
                'check_in' => now()->toDateString(),
                'check_out' => now()->addDay()->toDateString(),
                'min_days' => 1,
            ],
        ];

        foreach ($data['hotels'] as $item) {
            /** @var Hotel $hotel */
            $hotel = Hotel::factory()
                ->for($country)
                ->for($region)
                ->for($item['city'])
                ->hasAttached($item['tag'])
                ->create([
                    'name' => $item['name'],
                    'distance_city' => $item['distance_city'],
                    'min_days' => $item['min_days'],
                ]);

            foreach ($item['rooms'] as $room) {
                $isBooked = $room['is_booked'];
                unset($room['is_booked']);

                if ($isBooked) {
                    Booking::factory()
                        ->has(Room::factory()->state($room)->for($hotel))
                        ->for($hotel)
                        ->state(function () use ($item) {
                            return [
                                'check_in' => $item['check_in'],
                                'check_out' => $item['check_out'],
                            ];
                        })->create();
                }
            }
        }

        return $data;
    }

    public function testSearchHotelReturnsCorrectResponseForNotAuthUser(): void
    {
        /** @var City $city */
        $city = City::factory()->create();
        /** @var Hotel $hotel */
        $hotel = Hotel::factory()
            ->for($city)
            ->for($city->region)
            ->for($city->country)
            ->has(Room::factory())->create();

        $response = $this->postJson(route('api.hotels.search'), [
            'location' => $city->name,
        ])->assertOk();
        $response->assertJsonCount(1, 'data');

        $response->assertExactJson([
            'data' => [
                [
                    'id' => $hotel->getKey(),
                    'name' => $hotel->name,
                    'city' => $hotel->city->name,
                    'preview' => MediaService::create()->getPreview($hotel),
                    'min_price' => $hotel->getMinPriceRoom(),
                    'is_favorite' => false,
                    'is_new' => $hotel->isNew(),
                    'coordinates' => $hotel->coordinates ? explode(',', $hotel->coordinates) : null,
                ],
            ],
            'next_page' => null,
            'count' => 1,
        ]);
    }

    public function testSearchHotelReturnsCorrectResponseForAuthUser(): void
    {
        $user = User::factory()->create();
        /** @var City $city */
        $city = City::factory()->create();
        /** @var Hotel $hotel */
        $hotel = Hotel::factory()
            ->for($city)
            ->for($city->region)
            ->for($city->country)
            ->has(Room::factory())->create();
        $user->favoriteHotels()->sync($hotel);

        $response = $this->actingAs($user)->postJson(route('api.hotels.search'), [
            'location' => $city->name,
        ])->assertOk();
        $response->assertJsonCount(1, 'data');

        $response->assertExactJson([
            'data' => [
                [
                    'id' => $hotel->getKey(),
                    'name' => $hotel->name,
                    'city' => $hotel->city->name,
                    'preview' => MediaService::create()->getPreview($hotel),
                    'min_price' => $hotel->getMinPriceRoom(),
                    'is_favorite' => true,
                    'is_new' => $hotel->isNew(),
                    'coordinates' => $hotel->coordinates ? explode(',', $hotel->coordinates) : null,
                ],
            ],
            'next_page' => null,
            'count' => 1,
        ]);
    }
}
