<?php

namespace Tests\Feature\Hotel;

use App\Http\Services\MediaService;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class HotelFavoriteTest extends TestCase
{
    use DatabaseMigrations;

    private User $clientEmpty;
    private User $clientNotEmpty;
    private string $tokenForEmptyUser;
    private string $tokenForNotEmptyUser;
    private Hotel $hotel;

    public function setUp(): void
    {
        parent::setUp();

        $this->clientEmpty = User::factory()->asClient()->create();
        $this->clientNotEmpty = User::factory()->asClient()->create();
        $this->userLogin($this->clientEmpty);
        $this->userLogin($this->clientNotEmpty);
        $this->hotel = Hotel::factory()->create();
        $this->clientNotEmpty->favoriteHotels()->sync($this->hotel->getKey());
    }

    public function testIndexEmpty(): void
    {
        $this->actingAs($this->clientEmpty)->postJson(route('api.favorites.index'), [
            'page' => 1,
            'per_page' => 9,
        ])->assertOk()->assertJsonCount(0, 'data');
    }

    public function testIndexNotEmpty(): void
    {
        $this->actingAs($this->clientNotEmpty)->postJson(route('api.favorites.index'), [
            'page' => 1,
            'per_page' => 9,
        ])->assertOk()->assertExactJson([
            'data' => [
                [
                    'id' => $this->hotel->getKey(),
                    'name' => $this->hotel->name,
                    'city' => $this->hotel->city->name,
                    'preview' => MediaService::create()->getPreview($this->hotel),
                    'min_price' => $this->hotel->getMinPriceRoom(),
                    'is_favorite' => true,
                    'is_new' => $this->hotel->isNew(),
                    'coordinates' => $this->hotel->coordinates ? explode(',', $this->hotel->coordinates) : null,
                ],
            ],
            'next_page' => null,
        ]);
    }

    public function testIndexPaginate(): void
    {
        $hotels = Hotel::factory(config('nollo.per_page') + 1)->create();
        $this->clientNotEmpty->favoriteHotels()->sync($hotels);

        $this->actingAs($this->clientNotEmpty)->postJson(route('api.favorites.index'))
            ->assertOk()
            ->assertJsonCount(config('nollo.per_page'), 'data')
            ->assertJsonPath('next_page', 2);

        $this->actingAs($this->clientNotEmpty)->postJson(route('api.favorites.index'), ['page' => 2])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('next_page', null);
        $this->actingAs($this->clientNotEmpty)->postJson(route('api.favorites.index'), [
            'page' => 2,
            'per_page' => 4,
        ])
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('next_page', 3);
    }

    public function testIndexNotAuth(): void
    {
        $this->userLogOut();
        $this->postJson(route('api.favorites.index'))
            ->assertUnauthorized()
            ->assertExactJson([
                'message' =>  'Запрос не авторизован.',
            ]);
    }

    public function dataIndexInvalidRequest(): array
    {
        return [
            'invalid_page' => [
                ['page' => 'foo'],
                ['page'],
            ],
            'invalid_per_page' => [
                ['per_page' => 'foo'],
                ['per_page'],
            ],
        ];
    }

    /**
     * @dataProvider dataIndexInvalidRequest
     */
    public function testIndexValidation(array $requestInvalid): void
    {
        $this->actingAs($this->clientNotEmpty)
            ->postJson(route('api.favorites.index'), $requestInvalid)
            ->assertUnprocessable();
    }

    public function testStore(): void
    {
        $hotel = Hotel::factory()->create();

        $this->assertSame(1, $this->clientNotEmpty->favoriteHotels()->count());

        $this->actingAs($this->clientNotEmpty)->postJson(route('api.favorites.store', $hotel->getKey()), [
            'page' => 1,
            'per_page' => 9,
        ])->assertOk()->assertExactJson([
            'message' => 'Отель успешно добавлен в избранное.',
        ]);

        $this->assertSame(2, $this->clientNotEmpty->favoriteHotels()->count());

        $this->assertDatabaseHas('favorite_hotels', [
            'user_id' => $this->clientNotEmpty->getKey(),
            'hotel_id' => $hotel->getKey()]
        );
    }

    public function testStoreForSameHotel(): void
    {
        $this->assertSame(1, $this->clientNotEmpty->favoriteHotels()->count());

        $this->actingAs($this->clientNotEmpty)
            ->postJson(route('api.favorites.store', $this->hotel->getKey()), [
                'page' => 1,
                'per_page' => 9,
            ])->assertOk()->assertExactJson([
                'message' => 'Отель успешно добавлен в избранное.',
            ]);

        $this->assertSame(1, $this->clientNotEmpty->favoriteHotels()->count());

        $this->assertDatabaseHas('favorite_hotels', [
            'user_id' => $this->clientNotEmpty->getKey(),
            'hotel_id' => $this->hotel->getKey(),
        ]);
    }

    public function testStoreNotExistsHotel(): void
    {
        $this->actingAs($this->clientNotEmpty)->postJson(route('api.favorites.store', 101010101), [
            'page' => 1,
            'per_page' => 9,
        ])->assertNotFound();
    }

    public function testStoreNotAuth(): void
    {
        $this->userLogOut();
        $hotel = Hotel::factory()->create();

        $this->postJson(route('api.favorites.store', $hotel->getKey()))
            ->assertUnauthorized()
            ->assertExactJson([
                'message' =>  'Запрос не авторизован.',
            ]);
    }

    public function testRemove(): void
    {
        $this->assertSame(1, $this->clientNotEmpty->favoriteHotels()->count());

        $this->actingAs($this->clientNotEmpty)
            ->deleteJson(route('api.favorites.remove', $this->hotel->getKey()), [
                'page' => 1,
                'per_page' => 9,
            ])->assertOk()->assertExactJson([
                'message' => 'Отель успешно удален из избранного.',
            ]);

        $this->assertSame(0, $this->clientNotEmpty->favoriteHotels()->count());
    }

    public function testRemoveForNotFavoriteHotel(): void
    {
        $hotel = Hotel::factory()->create();

        $this->assertSame(1, $this->clientNotEmpty->favoriteHotels()->count());

        $this->actingAs($this->clientNotEmpty)->deleteJson(route('api.favorites.remove', $hotel->getKey()), [
            'page' => 1,
            'per_page' => 9,
        ])->assertOk()->assertExactJson([
            'message' => 'Отель успешно удален из избранного.',
        ]);

        $this->assertSame(1, $this->clientNotEmpty->favoriteHotels()->count());
    }

    public function testRemoveNotExistsHotel(): void
    {
        $this->actingAs($this->clientNotEmpty)->deleteJson(route('api.favorites.store', 101010101), [
            'page' => 1,
            'per_page' => 9,
        ])->assertNotFound();
    }

    public function testRemoveNotAuth(): void
    {
        $this->userLogOut();
        $hotel = Hotel::factory()->create();

        $this->deleteJson(route('api.favorites.remove', $hotel->getKey()))
            ->assertUnauthorized()
            ->assertExactJson([
                'message' =>  'Запрос не авторизован.',
            ]);
    }
}
