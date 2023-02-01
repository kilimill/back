<?php

namespace Tests\Feature\HotelOwner;

use App\Http\Resources\TagResource;
use App\Models\City;
use App\Models\Contact;
use App\Models\Hotel;
use App\Models\Lake;
use App\Models\Media;
use App\Models\Room;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HotelUpsertTest extends TestCase
{
    use DatabaseMigrations;

    private User $user;
    private Room $room;
    private Media $roomMediaBeforeUpdate;
    private array $hotelData;
    private array $requestData;
    private Collection $lakes;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->asClient()->create();
        $this->userLogin($this->user);
    }

    private function prepareDatabase(int $requestStatus): void
    {
        /** @var City $city */
        $city = City::factory()->create();
        $tags = Tag::factory(3)->create();
        $this->lakes = Lake::factory(2)->create();
        /** @var Room $room */
        $this->room = Room::factory()
            ->for(Hotel::factory()->for($this->user)
                ->withAddress()
                ->has(Tag::factory())
                ->has(Contact::factory(3)->state(new Sequence(
                    [
                        'type_id' => Contact::TYPE_ID_EMAIL,
                        'value' => 'contact_type_email@shouldbeupdated.ru',
                    ],
                    [
                        'type_id' => Contact::TYPE_ID_VK,
                        'value' => 'contact type vk - should not be updated',
                    ],
                    [
                        'type_id' => Contact::TYPE_ID_TELEGRAM,
                        'value' => 'contact type telegram - should not be updated',
                    ],
                )))
                ->hasAttached(Lake::factory(), ['distance_shore' => 300])
                ->create([
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                ])
            )->create([
                'name' => 'room name before update',
                'description' => 'room description before update',
                'guest_count' => 1,
                'meals_id' => Room::MEALS_ID_1,
                'price' => 1000,
                'price_weekend' => 1500,
            ]);

        $this->roomMediaBeforeUpdate = $this->room->media()->first();

        $this->hotelData = [
            'name' => 'hotel name',
            'type_id' => Hotel::TYPE_ID_HOTEL,
            'description' => 'some hotel description',
            'address' => json_encode([
                [
                    'kind' => 'country',
                    'name' => 'Россия',
                ],
                [
                    'kind' => 'province',
                    'name' => $city->region->name,
                ],
                [
                    'kind' => 'locality',
                    'name' => $city->name,
                ],
            ]),
//            'distance_city' => 123,
            'coordinates' => json_encode([-35.063639,107.343426]),
            'conditions' => 'some conditions',
            'detailed_route' => 'some detailed route',
            'season_id' => Hotel::SEASON_ID_FULL,
            'min_days' => 2,
            'check_in_hour' => 14,
            'check_out_hour' => 12,

            'status_id' => $requestStatus,
        ];

        Storage::fake('hotels');

        $this->requestData = $this->hotelData + [
                'tags' => $tags->pluck('id')->toArray(),

                'media' => [
                    UploadedFile::fake()->image('hotel1.jpg'),
                    UploadedFile::fake()->image('hotel2.jpg'),
                    UploadedFile::fake()->image('hotel3.jpg'),
                ],

                'contacts' => [
                    [
                        'id' => $this->room->hotel->contacts()
                            ->where('type_id', Contact::TYPE_ID_EMAIL)
                            ->first()
                            ->getKey(),
                        'type_id' => Contact::TYPE_ID_EMAIL,
                        'value' => 'contact_type_email@updated.ru',
                    ],
                    [
                        'type_id' => Contact::TYPE_ID_SITE,
                        'value' => 'contact type site - new',
                    ]
                ],

                'rooms' => [
                    [
                        'id' => $this->room->getKey(),
                        'name' => 'room name after update',
                        'description' => 'room description after update',
                        'guest_count' => 2,
                        'quantity' => 2,
                        'meals_id' => Room::MEALS_ID_2,
                        'price' => 2000,
                        'price_weekend' => 2500,
                        'media' => [
                            UploadedFile::fake()->image('room1.jpg'),
                        ],
                    ],
                    [
                        'name' => 'room name new',
                        'description' => 'room description new',
                        'guest_count' => 3,
                        'quantity' => 3,
                        'meals_id' => Room::MEALS_ID_3,
                        'price' => 3000,
                        'price_weekend' => 3500,
                        'media' => [
                            UploadedFile::fake()->image('room2.jpg'),
                        ],
                    ]
                ],

                'lakes' => [
                    [
                        'id' => $this->lakes->first()->getKey(),
                        'distance_shore' => 100,
                    ],
                    [
                        'id' => $this->lakes->last()->getKey(),
                        'distance_shore' => 200,
                    ],
                ],
            ];
    }

    private function getDataDbComparing(): array
    {
        $forDb = $this->hotelData;
        $address = $this->hotelData['address'] ? collect(json_decode($this->hotelData['address'], true))->mapWithKeys(function (array $item) {
            return [$item['kind'] => $item['name']];
        })->toArray() : null;

        $forDb['address'] = $address ? implode(', ', $address) : null;
        $forDb['coordinates'] = $this->hotelData['coordinates'] ? implode(',', json_decode($this->hotelData['coordinates'])) : null;

        return $forDb;
    }

    private function assertsBeforeUpdate(): void
    {
        // Hotel before update
        $this->assertCount(1, Hotel::all());
        $this->assertDatabaseMissing((new Hotel())->getTable(), $this->getDataDbComparing());

        // Count from seeds
        $this->assertCount(3, $this->room->hotel->media);

        // Tags before update
        $this->assertCount(1, $this->room->hotel->tags);

        // Contacts before update
        $this->assertCount(3, $this->room->hotel->contacts);

        // Lakes before update
        $this->assertCount(1, $this->room->hotel->lakes);

        // Rooms before update
        $this->assertCount(1, $this->room->hotel->rooms);

        // STATUS_ID_DRAFT before update
        $this->assertEquals(Hotel::STATUS_ID_DRAFT, $this->room->hotel->status_id);

        // Count from seeds
        $this->assertCount(1, $this->room->media);
    }

    private function assertsAfterUpdate(int $statusAfterUpdate): void
    {
        $hotel = $this->room->hotel;
        $hotel->refresh();

        // Hotel updated correctly
        $this->assertCount(1, Hotel::all());
        $this->assertDatabaseHas((new Hotel())->getTable(), $this->getDataDbComparing());

        // Images updated correctly. Count from seeds + 3 new
        $this->assertCount(6, $this->room->hotel->media);
        $this->assertDatabaseHas((new Media())->getTable(), [
            'model_type' => 'App\Models\Hotel',
            'model_id' => $this->room->hotel_id,
            'name' => 'hotel1',
            'custom_properties' => '{"preview":false}',
        ]);
        $this->assertDatabaseHas((new Media())->getTable(), [
            'model_type' => 'App\Models\Hotel',
            'model_id' => $this->room->hotel_id,
            'name' => 'hotel2',
            'custom_properties' => '{"preview":false}',
        ]);
        $this->assertDatabaseHas((new Media())->getTable(), [
            'model_type' => 'App\Models\Hotel',
            'model_id' => $this->room->hotel_id,
            'name' => 'hotel3',
            'custom_properties' => '{"preview":false}',
        ]);

        // Tags updated correctly
        $this->assertCount(3, $hotel->tags);

        // Contacts updated correctly
        $this->assertCount(4, $hotel->contacts);
        $this->assertDatabaseMissing((new Contact())->getTable(), [
            'hotel_id' => $hotel->getKey(),
            'type_id' => Contact::TYPE_ID_EMAIL,
            'value' => 'contact_type_email@shouldbeupdated.ru',
        ]);
        $this->assertDatabaseHas((new Contact())->getTable(), [
            'hotel_id' => $hotel->getKey(),
            'type_id' => Contact::TYPE_ID_EMAIL,
            'value' => 'contact_type_email@updated.ru',
        ]);
        $this->assertDatabaseHas((new Contact())->getTable(), [
            'hotel_id' => $hotel->getKey(),
            'type_id' => Contact::TYPE_ID_SITE,
            'value' => 'contact type site - new',
        ]);
        $this->assertDatabaseHas((new Contact())->getTable(), [
            'hotel_id' => $hotel->getKey(),
            'type_id' => Contact::TYPE_ID_TELEGRAM,
            'value' => 'contact type telegram - should not be updated',
        ]);
        $this->assertDatabaseHas((new Contact())->getTable(), [
            'hotel_id' => $hotel->getKey(),
            'type_id' => Contact::TYPE_ID_VK,
            'value' => 'contact type vk - should not be updated',
        ]);

        // Lakes updated correctly
        $this->assertCount(2, $this->room->hotel->lakes);
        $this->assertDatabaseMissing('hotel_lake', [
            'hotel_id' => $hotel->getKey(),
            'lake_id' => $this->lakes->first()->getKey(),
            'distance_shore' => 300,
        ]);
        $this->assertDatabaseHas('hotel_lake', [
            'hotel_id' => $hotel->getKey(),
            'lake_id' => $this->lakes->first()->getKey(),
            'distance_shore' => 100,
        ]);
        $this->assertDatabaseHas('hotel_lake', [
            'hotel_id' => $hotel->getKey(),
            'lake_id' => $this->lakes->last()->getKey(),
            'distance_shore' => 200,
        ]);

        // Rooms updated correctly
        $this->assertCount(5, $hotel->rooms);
        $this->assertDatabaseMissing((new Room())->getTable(), [
            'hotel_id' => $hotel->getKey(),
            'name' => 'room name before update',
            'description' => 'room description before update',
            'guest_count' => 1,
            'meals_id' => Room::MEALS_ID_1,
            'price' => 1000,
            'price_weekend' => 1500,
        ]);
        $this->assertDatabaseHas((new Room())->getTable(), [
            'hotel_id' => $hotel->getKey(),
            'name' => 'room name after update',
            'description' => 'room description after update',
            'guest_count' => 2,
            'meals_id' => Room::MEALS_ID_2,
            'price' => 2000,
            'price_weekend' => 2500,
        ]);
        $this->assertDatabaseHas((new Room())->getTable(), [
            'hotel_id' => $hotel->getKey(),
            'name' => 'room name new',
            'description' => 'room description new',
            'guest_count' => 3,
            'meals_id' => Room::MEALS_ID_3,
            'price' => 3000,
            'price_weekend' => 3500,
        ]);

        // Status after updated
        $this->assertEquals($statusAfterUpdate, $hotel->status_id);

        // Room images updated correctly
        $this->assertDatabaseMissing((new Media())->getTable(), [
            'model_type' => 'App\Models\Room',
            'model_id' => $this->roomMediaBeforeUpdate->model_id,
            'name' => $this->roomMediaBeforeUpdate->name,
            'custom_properties' => '{"preview":true}',
        ]);

        $updateRoom = Room::query()->where('name', 'room name after update')->first();
        $this->assertDatabaseHas((new Media())->getTable(), [
            'model_type' => 'App\Models\Room',
            'model_id' => $updateRoom->getKey(),
            'name' => 'room1',
            'custom_properties' => '{"preview":true}',
        ]);
        $newRoom = Room::query()->where('name', 'room name new')->first();
        $this->assertDatabaseHas((new Media())->getTable(), [
            'model_type' => 'App\Models\Room',
            'model_id' => $newRoom->getKey(),
            'name' => 'room2',
            'custom_properties' => '{"preview":true}',
        ]);
    }

    public function testOnlyHotelOwnerCanUpdateHotel(): void
    {
        $hotel = Hotel::factory()->create();

        $response = $this->postJson(route('api.owner.hotels.upsert', $hotel->getKey()), []);

        $response->assertNotFound()->assertExactJson([
            'message' =>  'Отель не найден.',
        ]);
    }

    public function testHotelCanBeCreated(): void
    {
        $this->prepareDatabase(Hotel::STATUS_ID_DRAFT);
        $this->assertCount(1, Hotel::all());

        $response = $this ->postJson(route('api.owner.hotels.upsert'), $this->requestData);

        $this->assertCount(2, Hotel::all());

        /** @var Hotel $hotel */
        $hotel = Hotel::query()->where('id', $response->collect()['data']['id'])->first();

        $response->assertOk()->assertExactJson([
            'message' => 'Данные успешно сохранены.',
            'data' => $this->getHotelResponse($hotel, Hotel::STATUS_ID_DRAFT),
            'valid' => [
                'index',
                'categories',
                'photos',
                'contacts',
                'address',
                'rooms',
                'lakes',
                'info',
                'extra',
            ],
        ]);
    }

    public function testHotelCanBeUpdatedAsDraft(): void
    {
        $status = Hotel::STATUS_ID_DRAFT;
        $this->prepareDatabase($status);
        $this->assertsBeforeUpdate();

        $response = $this->postJson(route('api.owner.hotels.upsert', $this->room->hotel_id), $this->requestData);

        $this->assertsAfterUpdate($status);

        $hotel = $this->room->hotel;

        $response->assertOk()->assertExactJson([
            'message' => 'Данные успешно сохранены.',
            'data' => $this->getHotelResponse($hotel, $status),
            'valid' => [
                'index',
                'categories',
                'photos',
                'contacts',
                'address',
                'rooms',
                'lakes',
                'info',
                'extra',
            ],
        ]);
    }

    public function testHotelHasFinalValidationBeforeSendingToReview(): void
    {
        $this->prepareDatabase(Hotel::STATUS_ID_UNDER_REVIEW);

        unset($this->requestData['name']);
        $hotel = $this->room->hotel;
        $hotel->name = null;
        $hotel->save();

        $response = $this->postJson(route('api.owner.hotels.upsert', $hotel->getKey()), $this->requestData);

        $response->assertUnprocessable()->assertJsonFragment([
            'errors' => [
                'index' => [
                    'name'=> [
                        'Поле Название обязательно для заполнения.'
                    ],
                ],
            ],
        ]);
    }

    public function testHotelHasFinalValidationForImagesBeforeSendingToReview(): void
    {
        $this->prepareDatabase(Hotel::STATUS_ID_UNDER_REVIEW);

        unset($this->requestData['media']);
        $hotel = $this->room->hotel;
        $hotel->clearMediaCollection('media');

        $response = $this->postJson(route('api.owner.hotels.upsert', $hotel->getKey()), $this->requestData);

        $response->assertUnprocessable()->assertJsonFragment([
            'errors' => [
                'photos' => [
                    'media'=> [
                        'Поле Фотографии обязательно для заполнения.'
                    ],
                ],
            ],
        ]);
    }

    public function testHotelCanBeUpdatedAndSendToReview(): void
    {
        $status = Hotel::STATUS_ID_UNDER_REVIEW;
        $this->prepareDatabase($status);
        $this->assertsBeforeUpdate();

        $response = $this->postJson(route('api.owner.hotels.upsert', $this->room->hotel_id), $this->requestData);

        $this->assertsAfterUpdate($status);

        $hotel = $this->room->hotel;

        $response->assertOk()->assertExactJson([
            'message' => 'Данные успешно сохранены.',
            'data' => $this->getHotelResponse($hotel, $status),
            'valid' => [
                'index',
                'categories',
                'photos',
                'contacts',
                'address',
                'rooms',
                'lakes',
                'info',
                'extra',
            ],
        ]);
    }

    public function testHotelCanBeUpdatedWithEmptyRequestAndSendToReview(): void
    {
        $status = Hotel::STATUS_ID_UNDER_REVIEW;
        $this->prepareDatabase($status);

        $response = $this->postJson(route('api.owner.hotels.upsert', $this->room->hotel_id), [
            'status_id' => Hotel::STATUS_ID_UNDER_REVIEW,
        ]);

        $hotel = $this->room->hotel;

        $response->assertOk()->assertExactJson([
            'message' => 'Данные успешно сохранены.',
            'data' => $this->getHotelResponse($hotel, $status),
            'valid' => [
                'index',
                'categories',
                'photos',
                'contacts',
                'address',
                'rooms',
                'lakes',
                'info',
                'extra',

            ],
        ]);

        $this->assertDatabaseHas((new Hotel())->getTable(), [
            'id' => $hotel->getKey(),
            'status_id' => Hotel::STATUS_ID_UNDER_REVIEW,
            'type_id' => $hotel->type_id,
            'name' => $hotel->name,
            'description' => $hotel->description,
            'country_id' => $hotel->country_id,
            'region_id' => $hotel->region_id,
            'city_id' => $hotel->city_id,
            'address' => $hotel->address,
            'coordinates' => $hotel->coordinates,
//            'distance_city' => $hotel->distance_city,
            'detailed_route' => $hotel->detailed_route,
            'conditions' => $hotel->conditions,
            'season_id' => $hotel->season_id,
            'min_days' => $hotel->min_days,
            'check_in_hour' => $hotel->check_in_hour,
            'check_out_hour' => $hotel->check_out_hour,
            'user_id' => $hotel->user_id,
        ]);
    }

    public function testUserGetRoleOwnerWhenCreatesHotel(): void
    {
        $user = User::factory()->asClient()->create();

        $this->assertDatabaseMissing((new User())->getTable(), [
            'id' => $user->getKey(),
            'role_id' => User::ROLE_ID_OWNER,
        ]);

        $response = $this->actingAs($user)->postJson(route('api.owner.hotels.upsert'), [
            'name' => 'new hotel',
            'status_id' => Hotel::STATUS_ID_DRAFT,
        ]);

        /** @var Hotel $hotel */
        $hotel = Hotel::query()->first();

        $response->assertOk()->assertExactJson([
            'message' => 'Данные успешно сохранены.',
            'data' => $this->getHotelResponse($hotel, Hotel::STATUS_ID_DRAFT),
            'valid' => [
                'lakes',
                'extra',
            ],
        ]);

        $this->assertDatabaseHas((new User())->getTable(), [
            'id' => $user->getKey(),
            'role_id' => User::ROLE_ID_OWNER,
        ]);
    }

    public function testUserDoesGetRoleOwnerWhenCreatesHotelAndError(): void
    {
        $user = User::factory()->asClient()->create();

        $this->assertDatabaseMissing((new User())->getTable(), [
            'id' => $user->getKey(),
            'role_id' => User::ROLE_ID_OWNER,
        ]);

        $this->actingAs($user)->postJson(route('api.owner.hotels.upsert'))->assertUnprocessable();

        $this->assertDatabaseMissing((new User())->getTable(), [
            'id' => $user->getKey(),
            'role_id' => User::ROLE_ID_OWNER,
        ]);
    }

    private function getHotelResponse(Hotel $hotel, int $status): array
    {
        return [
            'id' => $hotel->getKey(),
            'status_id' => $status,
            'type_id' => $hotel->type_id,
            'name' => $hotel->name,
            'description' => $hotel->description,
            'country_id' => $hotel->country_id,
            'region_id' => $hotel->region_id,
            'city_id' => $hotel->city_id,
            'address' => $hotel->address,
            'coordinates' => $hotel->coordinates ? explode(',', $hotel->coordinates) : null,
//            'distance_city' => $hotel->distance_city,
            'detailed_route' => $hotel->detailed_route,
            'conditions' => $hotel->conditions,
            'season_id' => $hotel->season_id,
            'min_days' => $hotel->min_days,
            'check_in_hour' => $hotel->check_in_hour,
            'check_out_hour' => $hotel->check_out_hour,
            'media' => $hotel->getMedia('media')->map(function (Media $file) {
                return [
                    'id' => $file->getKey(),
                    'url' => $file->getFullUrl(),
                    'is_preview' => $file->getCustomProperty('preview') ?? false,
                ];
            })->toArray(),
            'rooms' => $hotel->roomsGroup()->map(function (Room $room) {
                return [
                    'id' => $room->getKey(),
                    'group_id' => $room->group_id,
                    'name' => $room->name,
                    'description' => $room->description,
                    'guest_count' => $room->guest_count,
                    'meals_id' => $room->meals_id,
                    'price' => $room->price,
                    'price_weekend' => $room->price_weekend,
                    'quantity' => $room['quantity'],
                    'media' => $room->getMedia('media')->map(function (Media $file) {
                        return [
                            'id' => $file->getKey(),
                            'url' => $file->getFullUrl(),
                            'is_preview' => $file->getCustomProperty('preview') ?? false,
                        ];
                    })->toArray(),
                ];
            })->toArray(),
            'tags' =>$hotel->tags->pluck('id')->toArray(),
            'lakes' => $hotel->lakes->map(function (Lake $lake) {
                return [
                    'id' => $lake->getKey(),
                    'distance_shore' => $lake->pivot->distance_shore,
                ];
            })->toArray(),
            'contacts' => $hotel->contacts->map(function (Contact $contact) {
                return [
                    'id' => $contact->getKey(),
                    'type_id' => $contact->type_id,
                    'value' => $contact->value,
                ];
            })->toArray(),
        ];
    }
}
