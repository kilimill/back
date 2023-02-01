<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class LoadLocations extends Command
{
    protected $signature = 'load-locations';

    protected $description = 'Load country, regions, cities.';

    private int $countRegions = 0;
    private int $countCities = 0;
    private Country $country;
    private ?Collection $cities = null;

    public function handle()
    {
        $this->info('Started');
        try {
            $this->country = new Country();
            $this->country->id = 1;
            $this->country->name = 'Россия';
            $this->country->save();

            $regions = collect(json_decode(file_get_contents(base_path() . '/database/data/regions.json'), true));
            $regions->each(function (array $item) {
                $region = new Region();
                $region->id = $item['id'];
                $region->name = $item['name'];
                $region->country_id = $this->country->getKey();
                $region->save();
                $this->countRegions++;
                $this->line($region->name);
            });

            $cities = collect(json_decode(file_get_contents(base_path() . '/database/data/cities.json'), true));
            $cities->chunk(config('nollo.chunk'))->each(function (Collection $cities) {
                $cities->each(function (array $item) {
                    if ($this->isSkip($item['name'])) {
                        return;
                    }
                    $city = new City();
                    $city->id = $item['id'];
                    $city->name = $item['name'];
                    $city->region_id = $item['region_id'];
                    $city->country_id = $this->country->getKey();
                    $city->save();
                    $this->countCities++;
                    $this->line($city->name);
                });
            });
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->info('Finished');
        $this->info('Страна - ' . $this->country->name);
        $this->info('Количество регионов - ' .  $this->countRegions);
        $this->info('Количество населенных пунктов - ' .  $this->countCities);
    }

    private function isSkip(string $cityName): bool
    {
        return $this->cities && $this->cities->search($cityName) === false;
    }
}
