<?php

namespace Tests\Feature\HotelOwner;

use App\Http\Services\MediaService;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class HotelOwnerIndexTest extends TestCase
{
    use DatabaseMigrations;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->asOwner()->create();
        $this->userLogin($this->user);
        Hotel::factory(10)->for($this->user)->has(Room::factory())->create();
    }

    public function testHotelOwnerIndexEmpty(): void
    {
        $user = User::factory()->asOwner()->create();

        $this->actingAs($user)->postJson(route('api.owner.hotels.index'), [
            'page' => 1,
            'per_page' => 9,
        ])->assertOk()->assertJsonCount(0, 'data');
    }

    public function testHotelOwnerIndexShowsFirstPage(): void
    {
        $page = 1;
        $prePage = 9;

        $hotels = $this->user
            ->hotels()
            ->with('city', 'rooms')
            ->offset(($page - 1) * $prePage)
            ->limit($prePage)
            ->get();

        $response = $this->postJson(route('api.owner.hotels.index'), [
            'page' => $page,
            'per_page' => $prePage,
        ]);
        $response->assertOk();
        $response->assertJsonCount(9, 'data');

        $hotelsResponse = collect();
        $hotels->each(function (Hotel $hotel) use ($hotelsResponse) {
            $hotelsResponse->add([
                'id' => $hotel->getKey(),
                'status_id' => $hotel->status_id,
                'name' => $hotel->name,
                'city' => $hotel->city->name,
                'preview' => MediaService::create()->getPreview($hotel),
                'min_price' => $hotel->getMinPriceRoom(),
                'is_favorite' => false,
            ]);
        });

        $response->assertExactJson([
            'data' => $hotelsResponse->toArray(),
            'next_page' => 2,
        ]);
    }

    public function testHotelOwnerIndexShowsSecondPage(): void
    {
        $page = 2;
        $prePage = 9;

        $hotels = $this->user
            ->hotels()
            ->with('city', 'rooms')
            ->offset(($page - 1) * $prePage)
            ->limit($prePage)
            ->get();

        $response = $this->postJson(route('api.owner.hotels.index'), [
            'page' => $page,
            'per_page' => $prePage,
        ]);
        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        $hotelsResponse = collect();
        $hotels->each(function (Hotel $hotel) use ($hotelsResponse) {
            $hotelsResponse->add([
                'id' => $hotel->getKey(),
                'status_id' => $hotel->status_id,
                'name' => $hotel->name,
                'city' => $hotel->city->name,
                'preview' => MediaService::create()->getPreview($hotel),
                'min_price' => $hotel->getMinPriceRoom(),
                'is_favorite' => false,
            ]);
        });

        $response->assertExactJson([
            'data' => $hotelsResponse->toArray(),
            'next_page' => null,
        ]);
    }

    public function testHotelOwnerIndexNotAuth(): void
    {
        $this->userLogOut();
        $this->postJson(route('api.owner.hotels.index'))
            ->assertUnauthorized()
            ->assertExactJson(['message' =>  'Запрос не авторизован.']);
    }
}
