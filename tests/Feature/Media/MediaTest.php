<?php

namespace Tests\Feature\Media;

use App\Models\Hotel;
use App\Models\Media;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Testing\File;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use DatabaseMigrations;

    private Hotel $hotel;
    private Room $room;
    private Media $media;
    private File $image;

    public function setUp(): void
    {
        parent::setUp();

        $owner = User::factory()->create();
        $this->hotel = Hotel::factory()->for($owner)->create();
        $this->room = Room::factory()->for($this->hotel)->create();
        $this->userLogin($owner);
        $this->image = File::image('room.jpg');
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function testRemoveMediaFromHotel(): void
    {
        $this->hotel->addMedia($this->image)->toMediaCollection('test-preview');
        $media = $this->hotel->getMedia('test-preview');

        $this->assertDatabaseHas((new Media())->getTable(), [
            'id' => $media->first()->getKey(),
            'model_type' => 'App\\Models\\Hotel',
        ]);
        $this->assertCount(1, $media);
        $this->assertFileExists($media->first()->getPath());

        $this->deleteJson(route('api.hotels.media.remove', [
            'hotel' => $this->hotel,
            'media' => $media->first()->getKey(),
        ]))->assertOk()->assertExactJson([
            'message' => 'Вы успешно удалили картинку.',
        ]);

        $this->assertDatabaseMissing((new Media())->getTable(), ['id' => $media->first()->getKey()]);
        $this->assertCount(1, $media);
        $this->assertFileDoesNotExist($media->first()->getPath());
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function testRemoveMediaFromRoom(): void
    {
        $this->room->addMedia($this->image)->toMediaCollection('test-preview');
        $media = $this->room->getMedia('test-preview');

        $this->assertDatabaseHas((new Media())->getTable(), [
            'id' => $media->first()->getKey(),
            'model_type' => 'App\\Models\\Room',
        ]);
        $this->assertCount(1, $media);
        $this->assertFileExists($media->first()->getPath());

        $this->deleteJson(route('api.hotels.media.remove', [
            'hotel' => $this->hotel,
            'media' => $media->first()->getKey(),
        ]))->assertOk()->assertExactJson([
            'message' => 'Вы успешно удалили картинку.',
        ]);

        $this->assertDatabaseMissing((new Media())->getTable(), ['id' => $media->first()->getKey()]);
        $this->assertCount(1, $media);
        $this->assertFileDoesNotExist($media->first()->getPath());
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function testRemoveMediaNotAuth(): void
    {
        $this->userLogOut();
        $this->hotel->addMedia($this->image)->toMediaCollection('test-preview');
        $media = $this->hotel->getMedia('test-preview');

        $this->deleteJson(route('api.hotels.media.remove', [
            'hotel' => $this->hotel,
            'media' => $media->first()->getKey(),
        ]))->assertUnauthorized();
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function testRemoveMediaNotOwner(): void
    {
        /** @var Hotel $hotel */
        $hotel = Hotel::factory()->create();
        $hotel->addMedia($this->image)->toMediaCollection('test-preview');
        $media = $hotel->getMedia('test-preview');

        $this->deleteJson(route('api.hotels.media.remove', [
            'hotel' => $hotel,
            'media' => $media->first()->getKey(),
        ]))->assertNotFound();
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function testRemoveMediaNotFoundHotel(): void
    {
        $this->hotel->addMedia($this->image)->toMediaCollection('test-preview');
        $media = $this->hotel->getMedia('test-preview');

        $this->deleteJson(route('api.hotels.media.remove', [
            'hotel' => 100500,
            'media' => $media->first()->getKey(),
        ]))->assertNotFound();
    }

    public function testRemoveMediaNotFoundMedia(): void
    {
        $this->deleteJson(route('api.hotels.media.remove', [
            'hotel' => $this->hotel,
            'media' => 100500,
        ]))->assertNotFound();
    }
}
