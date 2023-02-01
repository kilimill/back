<?php

namespace Tests\Feature\Hotel;

use App\Http\Services\MediaService;
use App\Models\City;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class HotelIndexTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndexHotelLocationRequired(): void
    {
        $response = $this->postJson(route('api.hotels.index'));
        $response->assertUnprocessable()->assertInvalid('location');
    }

    public function testEmptyIndexHotel(): void
    {
        $this->postJson(route('api.hotels.index'), ['location' => 'test location'])
            ->assertOk()
            ->assertExactJson([
                'data' => [],
                'next_page' => null,
                'count' => 0,
            ]);
    }

    public function testIndexAllHotels(): void
    {
        $this->getJson(route('api.hotels.all'))
            ->assertOk();
//        TODO тест на кэш
    }

    public function testIndexPaginateHotel(): void
    {
        /** @var City $city */
        $city = City::factory()->create();
        Hotel::factory(config('nollo.per_page') + 1)
            ->for($city)
            ->for($city->region)
            ->for($city->country)
            ->has(Room::factory())->create();

        $this->postJson(route('api.hotels.index'), ['location' => $city->name])
            ->assertOk()
            ->assertJsonCount(config('nollo.per_page'), 'data')
            ->assertJsonPath('next_page', 2)
            ->assertJsonPath('count', config('nollo.per_page') + 1);

        $this->postJson(route('api.hotels.index', ['page' => 2, 'location' => $city->name]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('next_page', null)
            ->assertJsonPath('count', config('nollo.per_page') + 1);

        $this->postJson(route('api.hotels.index', ['page' => 2, 'per_page' => 4, 'location' => $city->name]))
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('next_page', 3)
            ->assertJsonPath('count', config('nollo.per_page') + 1);
    }

    public function testIndexHotelReturnsCorrectResponseForNotAuthUser(): void
    {
        /** @var City $city */
        $city = City::factory()->create();
        /** @var Hotel $hotel */
        $hotel = Hotel::factory()
            ->for($city)
            ->for($city->region)
            ->for($city->country)
            ->has(Room::factory())->create();

        $response = $this->postJson(route('api.hotels.index'), [
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

    public function testIndexHotelReturnsCorrectResponseForAuthUser(): void
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

        $response = $this->actingAs($user)->postJson(route('api.hotels.index'), [
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

    public function testIndexHotelReturnsDefaultLocationHotels(): void
    {
        /** @var City $city */
        $city = City::factory()->withName(config('nollo.default_search_location'))->create();
        /** @var Hotel $hotel */
        $hotel = Hotel::factory()
            ->for($city)
            ->for($city->region)
            ->for($city->country)
            ->has(Room::factory())->create();

        $response = $this->postJson(route('api.hotels.index'), [
            'location' => 'новый город',
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
}
