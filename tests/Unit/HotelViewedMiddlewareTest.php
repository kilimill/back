<?php

namespace Tests\Unit;

use App\Exceptions\ApiHotelOwnerException;
use App\Models\Hotel;
use App\Models\HotelViewed;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class HotelViewedMiddlewareTest extends TestCase
{
    use DatabaseMigrations;

    private User $user;
    private Hotel $hotel;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->asClient()->create();
        $this->userLogin($this->user);
        $this->hotel = Hotel::factory()->create();
    }

    public function testHotelCanBeMarkedAsViewedForAuthUser(): void
    {
        $this->assertCount(0, $this->user->viewedHotels);
        $this->getJson(route('api.hotels.show', $this->hotel))->assertOk();
        $this->user->refresh();
        $this->assertCount(1, $this->user->viewedHotels);
        $this->assertDatabaseHas((new HotelViewed())->getTable(), [
            'user_id' => $this->user->getKey(),
            'hotel_id' => $this->hotel->getKey(),
        ]);
    }

    public function testHotelCanNotBeMarkedAsViewedForNotAuthUser(): void
    {
        $this->userLogOut();

        $this->assertCount(0, $this->user->viewedHotels);
        $this->getJson(route('api.hotels.show', $this->hotel))->assertOk();
        $this->user->refresh();
        $this->assertCount(0, $this->user->viewedHotels);
        $this->assertDatabaseMissing((new HotelViewed())->getTable(), [
            'user_id' => $this->user->getKey(),
            'hotel_id' => $this->hotel->getKey(),
        ]);
    }

    public function testHotelViewedStoresOnlyActiveHotels(): void
    {
        /** @var Hotel $hotelDraft */
        $hotelDraft = Hotel::factory()->create(['status_id' => Hotel::STATUS_ID_DRAFT]);
        /** @var Hotel $hotelUnderReview */
        $hotelUnderReview = Hotel::factory()->create(['status_id' => Hotel::STATUS_ID_UNDER_REVIEW]);
        /** @var Hotel $hotelActive */
        $hotelActive = Hotel::factory()->create(['status_id' => Hotel::STATUS_ID_ACTIVE]);
        /** @var Hotel $hotelRejected */
        $hotelRejected = Hotel::factory()->create(['status_id' => Hotel::STATUS_ID_REJECTED]);
        $user = User::factory()->asClient()->create();

        $this->assertCount(0, $user->viewedHotels);
        $this->actingAs($user);
        $this->getJson(route('api.hotels.show', $hotelDraft))->assertOk();
        $this->getJson(route('api.hotels.show', $hotelUnderReview))->assertOk();
        $this->getJson(route('api.hotels.show', $hotelActive))->assertOk();
        $this->getJson(route('api.hotels.show', $hotelRejected))->assertOk();

        $viewedHotels = $user->viewedHotels()->get();
        $this->assertCount(1, $viewedHotels);
        $this->assertSame($hotelActive->getKey(), $viewedHotels->first()->getKey());
    }
}
