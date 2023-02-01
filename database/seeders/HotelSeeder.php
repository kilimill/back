<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\City;
use App\Models\Contact;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\Lake;
use App\Models\Room;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class HotelSeeder extends Seeder
{
    private Country $country;

    public function run(): void
    {
        $this->setCountry();
        $this->clearMedia();

        City::query()->with('region')->chunk(config('nollo.chunk'), function (Collection $cities) {
            $cities->each(function (City $city) {
                $this->createHotel(User::factory()->asOwner(), $city);
            });
        });
    }

    private function createHotel(Factory $userFactory, City $city): void
    {
        $roomsCount = 2;
        $bookingCount = 2;

        $hotels = Hotel::factory(10)
            ->for($userFactory)
            ->for($city)
            ->for($city->region)
            ->for($this->country)
            ->has(Room::factory($roomsCount)
                ->has(Booking::factory($bookingCount)
                    ->state(function (array $attributes, Room $room) {
                        return ['hotel_id' => $room->hotel_id];
                    })
                )
            )
            ->has(Contact::factory()->type('phone'))
            ->has(Contact::factory()->type('email'))
            ->hasAttached(Lake::query()->inRandomOrder()->first(), ['distance_shore' => 100])
            ->hasAttached(Tag::query()->inRandomOrder()->limit(3)->get())
            ->create();

        $this->createGroupedRooms($hotels);
    }

    private function createGroupedRooms(Collection $hotels): void
    {
        $hotels->each(function (Hotel $hotel) {
            Room::factory()->for($hotel)->create();

            $roomWithTwoChild = Room::factory()->for($hotel)->create();
            $roomWithTwoChildArray = $roomWithTwoChild->toArray();
            unset($roomWithTwoChildArray['id']);

            Room::factory()
                ->for($hotel)
                ->has(Booking::factory(2)->for($hotel))
                ->create($roomWithTwoChildArray);
            Room::factory()->for($hotel)->create($roomWithTwoChildArray);
        });
    }

    private function clearMedia(): void
    {
        $path = storage_path('app/media');
        if (File::exists($path)) {
            File::deleteDirectories($path);
        }

        Artisan::call('storage:link');
    }

    private function setCountry(): void
    {
        $this->country = Country::first();
    }
}
