<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;

class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    public function definition(): array
    {
        return [
            'status_id' => Hotel::STATUS_ID_ACTIVE,
            'type_id' => $this->faker->numberBetween(1, 3),
            'name' => ucfirst($this->faker->slug(2)),
            'description' => $this->faker->text,
            'country_id' => Country::factory(),
            'region_id' => Region::factory(),
            'city_id' => City::factory(),
            'address' => $this->faker->address,
            'coordinates' => $this->faker->latitude(55, 62) . ',' . $this->faker->longitude(40, 134),
//            'distance_city' => $this->faker->randomNumber(2),
            'detailed_route' => $this->faker->text,
            'conditions' => $this->faker->text,
            'season_id' => $this->faker->numberBetween(1, 3),
            'min_days' => $this->faker->numberBetween(1, 7),
            'check_in_hour' => $this->faker->numberBetween(0, 23),
            'check_out_hour' => $this->faker->numberBetween(0, 23),
            'user_id' => User::factory()->asOwner(),
        ];
    }

    public function existingLocation(): self
    {
        return $this->state(function () {
            /** @var Country $country */
            $country = Country::query()->inRandomOrder()->first();
            /** @var Region $region */
            $region = $country->regions()->inRandomOrder()->first();
            $city = $region->cities()->inRandomOrder()->first();

            return [
                'country_id' => $country->getKey(),
                'region_id' => $region->getKey(),
                'city_id' => $city->getKey(),
            ];
        });
    }

    public function withAddress(): self
    {
        return $this->state(function () {
            /** @var Country $country */
            $country = Country::findRussia();
            if (!$country) {
                $country = Country::factory()->withName('Россия')->create();
            }

            /** @var Region $region */
            $region = Region::factory()->for($country)->create();
            /** @var City $city */
            $city = City::factory()->for($country)->for($region)->create();

            return [
                'country_id' => $country->getKey(),
                'region_id' => $region->getKey(),
                'city_id' => $city->getKey(),
                'address' => $country->name. ', ' . $region->name . ', ' . $city->name,
            ];
        });
    }

    public function configure(): self
    {
        return $this->afterCreating(function (Hotel $hotel) {
            $directoryPreview = storage_path('seeds/hotels/preview');
            $filesPreview = File::files($directoryPreview);
            $directoryMedia = storage_path('seeds/hotels/media');
            $filesMedia = File::files($directoryMedia);

            $hotel->addMedia($filesPreview[rand(0,9)])->preservingOriginal()
                ->withCustomProperties(['preview' => true])
                ->toMediaCollection('media');
            $hotel->addMedia($filesMedia[rand(0,9)])->preservingOriginal()
                ->withCustomProperties(['preview' => false])
                ->toMediaCollection('media');
            $hotel->addMedia($filesMedia[rand(0,9)])->preservingOriginal()
                ->withCustomProperties(['preview' => false])
                ->toMediaCollection('media');
        });
    }
}
