<?php

namespace Tests\Feature\HotelOwner;

use App\Http\Resources\TagResource;
use App\Models\Contact;
use App\Models\Hotel;
use App\Models\Lake;
use App\Models\Media;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HotelRoomUpsertTest extends TestCase
{
    use DatabaseMigrations;

    private User $user;
    private Hotel $hotel;
    private Room $roomWithOutGroup;
    private Room $roomWithGroup;
    private Room $roomChild1;
    private Room $roomChild2;
    private array $requestData;
    private Media $roomWithOutGroupMediaBeforeUpdate;
    private Media $roomWithGroupMediaBeforeUpdate;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->asClient()->create();
        $this->userLogin($this->user);
    }

    private function prepareDatabase(int $requestStatus): void
    {
        $this->hotel = Hotel::factory()->for($this->user)->create([
            'status_id' => Hotel::STATUS_ID_DRAFT,
        ]);

        $this->roomWithOutGroup = Room::factory()->for($this->hotel)->create([
            'name' => 'roomWithOutChild name before update',
            'description' => 'roomWithOutChild description before update',
            'guest_count' => 1,
            'meals_id' => Room::MEALS_ID_1,
            'price' => 1000,
            'price_weekend' => 1500,
        ]);
        $this->roomWithOutGroupMediaBeforeUpdate = $this->roomWithOutGroup->media()->first();

        $this->roomWithGroup = Room::factory()->for($this->hotel)->create([
            'name' => 'roomWithTwoChild name before update',
            'description' => 'roomWithTwoChild description before update',
            'guest_count' => 1,
            'meals_id' => Room::MEALS_ID_1,
            'price' => 1000,
            'price_weekend' => 1500,
        ]);
        $this->roomWithGroupMediaBeforeUpdate = $this->roomWithGroup->media()->first();
        $this->roomChild1 = Room::factory()->for($this->hotel)->create([
            'group_id' => $this->roomWithGroup->getKey(),
            'name' => 'roomWithTwoChild name before update',
            'description' => 'roomWithTwoChild description before update',
            'guest_count' => 1,
            'meals_id' => Room::MEALS_ID_1,
            'price' => 1000,
            'price_weekend' => 1500,
        ]);
        $this->roomChild2 = Room::factory()->for($this->hotel)->create([
            'group_id' => $this->roomWithGroup->getKey(),
            'name' => 'roomWithTwoChild name before update',
            'description' => 'roomWithTwoChild description before update',
            'guest_count' => 1,
            'meals_id' => Room::MEALS_ID_1,
            'price' => 1000,
            'price_weekend' => 1500,
        ]);

        Storage::fake('rooms');

        $this->requestData = [
            'rooms' => [
                [
                    'id' => $this->roomWithOutGroup->getKey(),
                    'name' => 'roomWithOutChild name after update',
                    'description' => 'roomWithOutChild description after update',
                    'guest_count' => 2,
                    'meals_id' => Room::MEALS_ID_2,
                    'price' => 1001,
                    'price_weekend' => 1501,
                    'quantity' => 1,
                    'media' => [
                        UploadedFile::fake()->image('room1.jpg')
                    ],
                ],
                [
                    'id' => $this->roomWithGroup->getKey(),
                    'name' => 'roomWithTwoChild name after update',
                    'description' => 'roomWithTwoChild description after update',
                    'guest_count' => 4,
                    'meals_id' => Room::MEALS_ID_4,
                    'price' => 1004,
                    'price_weekend' => 1504,
                    'quantity' => 4,
                    'media' => [
                        UploadedFile::fake()->image('room4.jpg')
                    ],
                ],
                [
                    'name' => 'new room name',
                    'description' => 'new room description',
                    'guest_count' => 2,
                    'meals_id' => Room::MEALS_ID_2,
                    'price' => 1002,
                    'price_weekend' => 1502,
                    'quantity' => 2,
                    'media' => [
                        UploadedFile::fake()->image('room2.jpg')
                    ],
                ],
            ],

            'status_id' => $requestStatus,
        ];
    }

    private function assertsBeforeUpdate(): void
    {
        // Rooms before update
        $this->assertCount(4, $this->hotel->rooms);

        // STATUS_ID_DRAFT before update
        $this->assertEquals(Hotel::STATUS_ID_DRAFT, $this->hotel->status_id);

        $this->hotel->rooms->each(function (Room $room) {
            // Count from seeds
            $this->assertCount(1, $room->media()->get());
        });
    }

    private function assertsAfterUpdate(int $statusAfterUpdate): void
    {
        $this->hotel->refresh();

        // Rooms updated correctly
        $this->assertCount(7, $this->hotel->rooms);
        $this->assertDatabaseMissing((new Room())->getTable(), [
            'hotel_id' => $this->hotel->getKey(),
            'group_id' => null,
            'name' => 'roomWithOutChild name before update',
            'description' => 'roomWithOutChild description before update',
            'guest_count' => 1,
            'meals_id' => Room::MEALS_ID_1,
            'price' => 1000,
            'price_weekend' => 1500,
        ]);
        $this->assertDatabaseMissing((new Room())->getTable(), [
            'hotel_id' => $this->hotel->getKey(),
            'group_id' => $this->roomWithOutGroup->getKey(),
            'name' => 'roomWithTwoChild name before update',
            'description' => 'roomWithTwoChild description before update',
            'guest_count' => 1,
            'meals_id' => Room::MEALS_ID_1,
            'price' => 1000,
            'price_weekend' => 1500,
        ]);
        $this->assertDatabaseMissing((new Room())->getTable(), [
            'hotel_id' => $this->hotel->getKey(),
            'group_id' => $this->roomWithGroup->getKey(),
            'name' => 'roomWithTwoChild name before update',
            'description' => 'roomWithTwoChild description before update',
            'guest_count' => 1,
            'meals_id' => Room::MEALS_ID_1,
            'price' => 1000,
            'price_weekend' => 1500,
        ]);
        $this->assertDatabaseMissing((new Room())->getTable(), [
            'hotel_id' => $this->hotel->getKey(),
            'group_id' => $this->roomWithGroup->getKey(),
            'name' => 'roomWithTwoChild name before update',
            'description' => 'roomWithTwoChild description before update',
            'guest_count' => 1,
            'meals_id' => Room::MEALS_ID_1,
            'price' => 1000,
            'price_weekend' => 1500,
        ]);

        $this->assertDatabaseHas((new Room())->getTable(), [
            'hotel_id' => $this->hotel->getKey(),
            'group_id' => $this->roomWithOutGroup->getKey(),
            'name' => 'roomWithOutChild name after update',
            'description' => 'roomWithOutChild description after update',
            'guest_count' => 2,
            'meals_id' => Room::MEALS_ID_2,
            'price' => 1001,
            'price_weekend' => 1501,
        ]);
        $this->assertDatabaseHas((new Room())->getTable(), [
            'hotel_id' => $this->hotel->getKey(),
            'group_id' => $this->roomWithGroup->getKey(),
            'name' => 'roomWithTwoChild name after update',
            'description' => 'roomWithTwoChild description after update',
            'guest_count' => 4,
            'meals_id' => Room::MEALS_ID_4,
            'price' => 1004,
            'price_weekend' => 1504,
        ]);
        $this->assertDatabaseHas((new Room())->getTable(), [
            'hotel_id' => $this->hotel->getKey(),
            'group_id' => $this->roomWithGroup->getKey(),
            'name' => 'roomWithTwoChild name after update',
            'description' => 'roomWithTwoChild description after update',
            'guest_count' => 4,
            'meals_id' => Room::MEALS_ID_4,
            'price' => 1004,
            'price_weekend' => 1504,
        ]);
        $this->assertDatabaseHas((new Room())->getTable(), [
            'hotel_id' => $this->hotel->getKey(),
            'group_id' => $this->roomWithGroup->getKey(),
            'name' => 'roomWithTwoChild name after update',
            'description' => 'roomWithTwoChild description after update',
            'guest_count' => 4,
            'meals_id' => Room::MEALS_ID_4,
            'price' => 1004,
            'price_weekend' => 1504,
        ]);
        $this->assertDatabaseHas((new Room())->getTable(), [
            'hotel_id' => $this->hotel->getKey(),
            'group_id' => $this->roomWithGroup->getKey(),
            'name' => 'roomWithTwoChild name after update',
            'description' => 'roomWithTwoChild description after update',
            'guest_count' => 4,
            'meals_id' => Room::MEALS_ID_4,
            'price' => 1004,
            'price_weekend' => 1504,
        ]);

        $newRoomsGroup = Room::query()
            ->where('hotel_id', $this->hotel->getKey())
            ->where('name', 'new room name')
            ->get();
        /** @var Room $newRoomGroup1 */
        $newRoomGroup1 = $newRoomsGroup->first();
        /** @var Room $newRoomGroup2 */
        $newRoomGroup2 = $newRoomsGroup->last();
        $this->assertDatabaseHas((new Room())->getTable(), [
            'hotel_id' => $this->hotel->getKey(),
            'group_id' => $newRoomGroup1->getKey(),
            'name' => 'new room name',
            'description' => 'new room description',
            'guest_count' => 2,
            'meals_id' => Room::MEALS_ID_2,
            'price' => 1002,
            'price_weekend' => 1502,
        ]);

        $this->assertDatabaseHas((new Room())->getTable(), [
            'hotel_id' => $this->hotel->getKey(),
            'group_id' => $newRoomGroup1->getKey(),
            'name' => 'new room name',
            'description' => 'new room description',
            'guest_count' => 2,
            'meals_id' => Room::MEALS_ID_2,
            'price' => 1002,
            'price_weekend' => 1502,
        ]);

        // Status after updated
        $this->assertEquals($statusAfterUpdate, $this->hotel->status_id);

        // Images updated
        $this->assertCount(1, $this->roomWithOutGroup->media()->get());
        $this->assertCount(1, $this->roomWithGroup->media()->get());
        $this->assertCount(1, $newRoomGroup1->media()->get());
        $this->assertCount(1, $newRoomGroup2->media()->get());

        $this->assertDatabaseMissing((new Media())->getTable(), [
            'model_type' => 'App\Models\Room',
            'model_id' => $this->roomWithOutGroupMediaBeforeUpdate->model_id,
            'name' => $this->roomWithOutGroupMediaBeforeUpdate->name,
            'custom_properties' => '{"preview":true}',
        ]);
        $this->assertDatabaseMissing((new Media())->getTable(), [
            'model_type' => 'App\Models\Room',
            'model_id' => $this->roomWithGroupMediaBeforeUpdate->model_id,
            'name' => $this->roomWithGroupMediaBeforeUpdate->name,
            'custom_properties' => '{"preview":true}',
        ]);

        $this->assertDatabaseHas((new Media())->getTable(), [
            'model_type' => 'App\Models\Room',
            'model_id' => $this->roomWithOutGroup->getKey(),
            'name' => 'room1',
            'custom_properties' => '{"preview":true}',
        ]);
        $this->assertDatabaseHas((new Media())->getTable(), [
            'model_type' => 'App\Models\Room',
            'model_id' => $this->roomWithGroup->getKey(),
            'name' => 'room4',
            'custom_properties' => '{"preview":true}',
        ]);
        $this->assertDatabaseHas((new Media())->getTable(), [
            'model_type' => 'App\Models\Room',
            'model_id' => $newRoomGroup1->getKey(),
            'name' => 'room2',
            'custom_properties' => '{"preview":true}',
        ]);
        $this->assertDatabaseHas((new Media())->getTable(), [
            'model_type' => 'App\Models\Room',
            'model_id' => $newRoomGroup2->getKey(),
            'name' => 'room2',
            'custom_properties' => '{"preview":true}',
        ]);
    }

    public function testHotelRoomsCanBeCreated(): void
    {
        $this->prepareDatabase(Hotel::STATUS_ID_DRAFT);
        $this->assertCount(4, Room::all());

        $response = $this->postJson(route('api.owner.hotels.upsert'), $this->requestData);

        $this->assertCount(11, Room::all());
        $this->assertCount(7, Room::query()->where('hotel_id', 2)->get());

        /** @var Hotel $hotel */
        $hotel = Hotel::query()->where('id', $response->collect()['data']['id'])->first();

        $response->assertOk()->assertExactJson([
            'message' => 'Данные успешно сохранены.',
            'data' => $this->getHotelResponse($hotel, Hotel::STATUS_ID_DRAFT),
            'valid' => [
                'rooms',
                'lakes',
                'extra',
            ],
        ]);
    }

    public function testHotelRoomsCanBeUpdatedAsDraft(): void
    {
        $status = Hotel::STATUS_ID_DRAFT;
        $this->prepareDatabase($status);
        $this->assertsBeforeUpdate();

        $response = $this->postJson(route('api.owner.hotels.upsert', $this->hotel->getKey()), $this->requestData);

        $this->assertsAfterUpdate($status);

        $response->assertOk()->assertExactJson([
            'message' => 'Данные успешно сохранены.',
            'data' => $this->getHotelResponse($this->hotel, Hotel::STATUS_ID_DRAFT),
            'valid' => [
                'index',
                'photos',
                'address',
                'rooms',
                'lakes',
                'info',
                'extra'

            ],
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
