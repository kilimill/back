<?php

namespace Tests\Feature\Hotel;

use App\Http\Resources\TagResource;
use App\Http\Services\HotelService;
use App\Models\Contact;
use App\Models\Hotel;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class HotelShowTest extends TestCase
{
    use DatabaseMigrations;

    public function testShowHotelForNotAuthUser(): void
    {
        /** @var Hotel $hotel */
        $hotel = Hotel::factory()->create();
        Contact::factory(2)->for($hotel)->create();
        $hotelService = HotelService::create();
        $hotelContacts = $hotelService->getContacts($hotel);

        $response = $this->getJson(route('api.hotels.show', $hotel));

        $response->assertOk()->assertExactJson([
            'data' => [
                'name' => $hotel->name,
                'media' => $hotel->getMedia('media')->map(function (Media $file) {
                    return [
                        'id' => $file->getKey(),
                        'url' => $file->getFullUrl(),
                        'is_preview' => $file->getCustomProperty('preview') ?? false,
                    ];
                })->toArray(),
                'description' => $hotel->description,
                'address' => $hotel->address,
                'detailed_route' => $hotel->detailed_route,
                'coordinates' =>  explode(',', $hotel->coordinates),
                'contacts' => [
                    $hotelContacts[0],
                    $hotelContacts[1],
                ],
                'tags' => TagResource::collection($hotel->tags),
                'extra' => $hotel->extra(),
                'is_favorite' => false,
                'is_new' => $hotel->isNew(),
            ],
        ]);
    }

    public function testShowHotelForAuthUser(): void
    {
        /** @var Hotel $hotel */
        $hotel = Hotel::factory()->create();

        $user = User::factory()->create();
        $user->favoriteHotels()->sync($hotel);

        Contact::factory(2)->for($hotel)->create();
        $hotelService = HotelService::create();
        $hotelContacts = $hotelService->getContacts($hotel);

        $response = $this->actingAs($user)->getJson(route('api.hotels.show', $hotel));

        $response->assertOk()->assertExactJson([
            'data' => [
                'name' => $hotel->name,
                'media' => $hotel->getMedia('media')->map(function (Media $file) {
                    return [
                        'id' => $file->getKey(),
                        'url' => $file->getFullUrl(),
                        'is_preview' => $file->getCustomProperty('preview') ?? false,
                    ];
                })->toArray(),
                'description' => $hotel->description,
                'address' => $hotel->address,
                'detailed_route' => $hotel->detailed_route,
                'coordinates' =>  explode(',', $hotel->coordinates),
                'contacts' => [
                    $hotelContacts[0],
                    $hotelContacts[1],
                ],
                'tags' => TagResource::collection($hotel->tags),
                'extra' => $hotel->extra(),
                'is_favorite' => true,
                'is_new' => $hotel->isNew(),
            ],
        ]);
    }

    public function testShowHotelNotExists(): void
    {
        $hotel = Hotel::factory()->create();

        $response = $this->getJson(route('api.hotels.show', $hotel->getKey() + 1));

        $response->assertNotFound();
    }
}
