<?php

namespace Tests\Feature\Hotel;

use App\Http\Services\MediaService;
use App\Models\Hotel;
use App\Models\HotelViewed;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class HotelViewedTest extends TestCase
{
    use DatabaseMigrations;

    public function testHotelViewedReturnsHotelsInCorrectOrderAndGrouped(): void
    {
        /** @var Hotel $hotel1 */
        $hotel1 = Hotel::factory()->create();
        /** @var Hotel $hotel2 */
        $hotel2 = Hotel::factory()->create();
        /** @var Hotel $hotel3 */
        $hotel3 = Hotel::factory()->create();

        $user = User::factory()->asClient()->create();
        $user->viewedHotels()->attach($hotel1);
        $user->viewedHotels()->attach($hotel1);
        $user->viewedHotels()->attach($hotel2);
        $user->viewedHotels()->attach($hotel3);
        $user->viewedHotels()->attach($hotel2);

        HotelViewed::query()->each(function (HotelViewed $viewedHotel, int $key) {
            $viewedHotel->created_at = now()->subMinutes(5-$key);
            $viewedHotel->save();
        });

        $response = $this->actingAs($user)->getJson(route('api.hotels.viewed.index'))->assertOk();

        $response->assertExactJson([
            'data' => [
                [
                    'id' => $hotel2->getKey(),
                    'name' => $hotel2->name,
                    'city' => $hotel2->city->name,
                    'preview' => MediaService::create()->getPreview($hotel2),
                    'min_price' => $hotel2->getMinPriceRoom(),
                    'is_favorite' => false,
                    'is_new' => $hotel2->isNew(),
                    'coordinates' => $hotel2->coordinates ? explode(',', $hotel2->coordinates) : null,
                ],
                [
                    'id' => $hotel3->getKey(),
                    'name' => $hotel3->name,
                    'city' => $hotel3->city->name,
                    'preview' => MediaService::create()->getPreview($hotel3),
                    'min_price' => $hotel3->getMinPriceRoom(),
                    'is_favorite' => false,
                    'is_new' => $hotel3->isNew(),
                    'coordinates' => $hotel3->coordinates ? explode(',', $hotel3->coordinates) : null,
                ],
                [
                    'id' => $hotel1->getKey(),
                    'name' => $hotel1->name,
                    'city' => $hotel1->city->name,
                    'preview' => MediaService::create()->getPreview($hotel1),
                    'min_price' => $hotel1->getMinPriceRoom(),
                    'is_favorite' => false,
                    'is_new' => $hotel1->isNew(),
                    'coordinates' => $hotel1->coordinates ? explode(',', $hotel1->coordinates) : null,
                ],
            ],
        ]);
    }

    public function testHotelViewedCanReturnEmptyArray(): void
    {
        $user = User::factory()->asClient()->create();
        $response = $this->actingAs($user)->getJson(route('api.hotels.viewed.index'))->assertOk();

        $response->assertExactJson([
            'data' => [],
        ]);
    }
}
