<?php

namespace Tests\Feature\HotelOwner;

use App\Http\Resources\TagResource;
use App\Models\Contact;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\Lake;
use App\Models\Media;
use App\Models\Room;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class HotelOwnerShowTest extends TestCase
{
    use DatabaseMigrations;

    public function testHotelShowReturnsCorrectResponse(): void
    {
        /** @var Hotel $hotel */
        $hotel = Hotel::factory()
            ->has(Tag::factory(2))
            ->has(Contact::factory(2)->state(new Sequence(
                [
                    'type_id' => Contact::TYPE_ID_PHONE,
                    'value' => '78888888888',
                ],
                [
                    'type_id' => Contact::TYPE_ID_EMAIL,
                    'value' => 'contact_type_email@shouldbeupdated.ru',
                ],
            )))
            ->for(Country::factory()->withName('Россия'))
            ->hasAttached(Lake::factory(2), ['distance_shore' => 100])
            ->create();

        /** @var Room $roomWithOutGroup */
        $roomWithOutGroup = Room::factory()->for($hotel)->create();

        /** @var Room $roomWithGroup */
        $roomWithGroup = Room::factory()->for($hotel)->create();

        Room::factory()->for($hotel)->create([
            'group_id' => $roomWithGroup->getKey(),
        ]);
        Room::factory()->for($hotel)->create([
            'group_id' => $roomWithGroup->getKey(),
        ]);

        /** @var Lake $lakeFirst */
        $lakeFirst = $hotel->lakes->first();
        /** @var Lake $lakeLast */
        $lakeLast = $hotel->lakes->last();

        /** @var Contact $contactFirst */
        $contactFirst = $hotel->contacts->first();
        /** @var Contact $contactLast */
        $contactLast = $hotel->contacts->last();

        $response = $this->actingAs($hotel->user)->getJson(route('api.owner.hotels.show', $hotel));
        $response->assertOk()->assertExactJson([
            'data' => [
                'id' => $hotel->getKey(),
                'status_id' => $hotel->status_id,
                'type_id' => $hotel->type_id,
                'name' => $hotel->name,
                'description' => $hotel->description,
                'country_id' => $hotel->country_id,
                'region_id' => $hotel->region_id,
                'city_id' => $hotel->city_id,
                'address' => $hotel->address,
                'coordinates' => $hotel->coordinates ? explode(',', $hotel->coordinates) : null,
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
                'rooms' => [
                    [
                        'id' => $roomWithOutGroup->getKey(),
                        'group_id' => $roomWithOutGroup->group_id,
                        'name' => $roomWithOutGroup->name,
                        'description' => $roomWithOutGroup->description,
                        'guest_count' => $roomWithOutGroup->guest_count,
                        'meals_id' => $roomWithOutGroup->meals_id,
                        'price' => $roomWithOutGroup->price,
                        'price_weekend' => $roomWithOutGroup->price_weekend,
                        'quantity' => 1,
                        'media' => $roomWithOutGroup->getMedia('media')->map(function (Media $file) {
                            return [
                                'id' => $file->getKey(),
                                'url' => $file->getFullUrl(),
                                'is_preview' => $file->getCustomProperty('preview') ?? false,
                            ];
                        })->toArray(),
                    ],
                    [
                        'id' => $roomWithGroup->getKey(),
                        'group_id' => $roomWithGroup->group_id,
                        'name' => $roomWithGroup->name,
                        'description' => $roomWithGroup->description,
                        'guest_count' => $roomWithGroup->guest_count,
                        'meals_id' => $roomWithGroup->meals_id,
                        'price' => $roomWithGroup->price,
                        'price_weekend' => $roomWithGroup->price_weekend,
                        'quantity' => 3,
                        'media' => $roomWithGroup->getMedia('media')->map(function (Media $file) {
                            return [
                                'id' => $file->getKey(),
                                'url' => $file->getFullUrl(),
                                'is_preview' => $file->getCustomProperty('preview') ?? false,
                            ];
                        })->toArray(),
                    ],
                ],
                'tags' => [
                    $hotel->tags->first()->getKey(),
                    $hotel->tags->last()->getKey(),
                ],
                'lakes' => [
                    [
                        'id' => $lakeFirst->getKey(),
                        'distance_shore' => 100,
                    ],
                    [
                        'id' => $lakeLast->getKey(),
                        'distance_shore' => 100,
                    ],
                ],
                'contacts' => [
                    [
                        'id' => $contactFirst->getKey(),
                        'type_id' => $contactFirst->type_id,
                        'value' => $contactFirst->value,
                    ],
                    [
                        'id' => $contactLast->getKey(),
                        'type_id' => $contactLast->type_id,
                        'value' => $contactLast->value,
                    ],
                ],
            ],
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

    public function testHotelShowDoesNotReturnNowOwnHotels(): void
    {
        $hotel = Hotel::factory()->create();
        $user = User::factory()->create();
        $response = $this->actingAs($user)->getJson(route('api.owner.hotels.show', $hotel));
        $response->assertNotFound();
    }

    public function testHotelShowReturnsValidation(): void
    {
        $user = User::factory()->asOwner()->create();

        $hotel = new Hotel();
        $hotel->status_id = Hotel::STATUS_ID_UNDER_REVIEW;
        $hotel->user_id = $user->getKey();
        $hotel->save();

        $response = $this->actingAs($user)->getJson(route('api.owner.hotels.show', $hotel));
        $response->assertOk()->assertExactJson([
            'data' => [
                'id' => $hotel->getKey(),
                'status_id' => Hotel::STATUS_ID_UNDER_REVIEW,
                'type_id' => null,
                'name' => null,
                'description' => null,
                'country_id' => null,
                'region_id' => null,
                'city_id' => null,
                'address' => null,
                'coordinates' => null,
                'detailed_route' => null,
                'conditions' => null,
                'season_id' => null,
                'min_days' => null,
                'check_in_hour' => null,
                'check_out_hour' => null,
                'media' => [],
                'rooms' => [],
                'tags' => [],
                'lakes' => [],
                'contacts' => [],
            ],
            'valid' => [
                'lakes',
                'extra',

            ],
        ]);
    }
}
