<?php

namespace Tests\Feature\Media;

use App\Models\Hotel;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Testing\File;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Tests\TestCase;

class MediaUpdateMainTest extends TestCase
{
    use DatabaseMigrations;

    private Hotel $hotel;
    private Media $media;

    public function setUp(): void
    {
        parent::setUp();

        $owner = User::factory()->create();
        $this->hotel = Hotel::factory()->for($owner)->create();
        $this->userLogin($owner);
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function testAddMainMediaForHotel(): void
    {
        $media1 = $this->hotel->addMedia(File::image('hotel1.jpg'))
            ->withCustomProperties(['preview' => true])
            ->toMediaCollection('media');

        $media2 = $this->hotel->addMedia(File::image('hotel2.jpg'))
            ->withCustomProperties(['preview' => false])
            ->toMediaCollection('media');

        $this->postJson(route('api.hotels.media.addMain', [
            'hotel' => $this->hotel,
            'media' => $media2,
        ]))->assertOk()->assertExactJson([
            'message' => 'Вы успешно отметили главную фотографию.',
        ]);

        $media1->refresh();
        $media2->refresh();
        $this->assertEquals(false, $media1->getCustomProperty('preview'));
        $this->assertEquals(true, $media2->getCustomProperty('preview'));
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function testRemoveMainMediaForHotel(): void
    {
        $media1 = $this->hotel->addMedia(File::image('hotel1.jpg'))
            ->withCustomProperties(['preview' => true])
            ->toMediaCollection('media');

        $media2 = $this->hotel->addMedia(File::image('hotel2.jpg'))
            ->withCustomProperties(['preview' => false])
            ->toMediaCollection('media');

        $this->deleteJson(route('api.hotels.media.removeMain', [
            'hotel' => $this->hotel,
            'media' => $media1,
        ]))->assertOk()->assertExactJson([
            'message' => 'Вы успешно убрали отметку главная фотография.',
        ]);

        $media1->refresh();
        $media2->refresh();
        $this->assertEquals(false, $media1->getCustomProperty('preview'));
        $this->assertEquals(false, $media2->getCustomProperty('preview'));
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function testUserCanNotAddMainMediaForWrongHotel(): void
    {
        /** @var Hotel $wrongHotel */
        $wrongHotel = Hotel::factory()->create();
        $media = $this->hotel->addMedia(File::image('hotel1.jpg'))
            ->withCustomProperties(['preview' => true])
            ->toMediaCollection('media');

        $response = $this->actingAs($wrongHotel->user)->postJson(route('api.hotels.media.addMain', [
            'hotel' => $wrongHotel,
            'media' => $media,
        ]))->assertUnprocessable();

        $response->assertJsonFragment([
            'errors' => [
                'media' => [
                    'Картинка не найдена.',
                ],
            ],
        ]);
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function testUserCanNotRemoveMainMediaForWrongHotel(): void
    {
        /** @var Hotel $wrongHotel */
        $wrongHotel = Hotel::factory()->create();
        $media = $this->hotel->addMedia(File::image('hotel1.jpg'))
            ->withCustomProperties(['preview' => true])
            ->toMediaCollection('media');

        $response = $this->actingAs($wrongHotel->user)->deleteJson(route('api.hotels.media.removeMain', [
            'hotel' => $wrongHotel,
            'media' => $media,
        ]))->assertUnprocessable();

        $response->assertJsonFragment([
            'errors' => [
                'media' => [
                    'Картинка не найдена.',
                ],
            ],
        ]);
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function testUserMustBeHotelOwnerToAddMainMedia(): void
    {
        /** @var Hotel $wrongHotel */
        $wrongHotel = Hotel::factory()->create();
        $media = $this->hotel->addMedia(File::image('hotel1.jpg'))
            ->withCustomProperties(['preview' => true])
            ->toMediaCollection('media');

        $response = $this->postJson(route('api.hotels.media.addMain', [
            'hotel' => $wrongHotel,
            'media' => $media,
        ]))->assertNotFound();

        $response->assertExactJson([
            'message' =>  'Отель не найден.',
        ]);
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function testUserMustBeHotelOwnerToRemoveMainMedia(): void
    {
        /** @var Hotel $wrongHotel */
        $wrongHotel = Hotel::factory()->create();
        $media = $this->hotel->addMedia(File::image('hotel1.jpg'))
            ->withCustomProperties(['preview' => true])
            ->toMediaCollection('media');

        $response = $this->deleteJson(route('api.hotels.media.removeMain', [
            'hotel' => $wrongHotel,
            'media' => $media,
        ]))->assertNotFound();

        $response->assertExactJson([
            'message' =>  'Отель не найден.',
        ]);
    }
}
