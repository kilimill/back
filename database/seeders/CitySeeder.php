<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class CitySeeder extends Seeder
{
    private Country $country;
    private ?Collection $cities = null;

    public function run(): void
    {
        $this->checkSeedMinimal();

        $this->country = Country::factory()->withName('Россия')->create();

        $cities = collect(json_decode(file_get_contents(base_path().'/database/seeders/FakeData/cities.json'), true));
        $cities->chunk(config('nollo.chunk'))->each(function (Collection $cities) {
            $cities->each(function (array $item) {
                if ($this->isSkip($item['city'])) {
                    return;
                }

                if (!$region = $this->findRegion($regionName = $item['region'])) {
                    $region = $this->createRegion($regionName);
                }

                $this->createCity($item['city'], $region);
            });
        });
    }

    private function findRegion(string $name): ?Region
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Region::query()->where('name', $name)->first();
    }

    private function createRegion(string $name): Region
    {
        return Region::factory()->for($this->country)->withName($name)->create();
    }

    private function createCity(string $name, Region $region): void
    {
        City::factory()->for($region)->for($this->country)->withName($name)->create();
    }

    private function checkSeedMinimal(): void
    {
        $cities = config('nollo.seed.cities');

        if (config('nollo.seed.minimal')) {
           if ($cities !== '') {
               $this->cities = collect(explode(',', $cities));
           } else {
               $this->cities = collect([
                   'Москва',
               ]);
           }
        }
    }

    private function isSkip(string $cityName): bool {
        return $this->cities && $this->cities->search($cityName) === false;
    }
}
