<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CityTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndexCities(): void
    {
        $country = Country::factory()->create(['name' => 'Россия']);
        $region = Region::factory()->for($country)->create();
        $cities = City::factory(10)->for($country)->for($region)->create();

        /** @var City $firstCity */
        $firstCity = $cities->first();

        $this->getJson(route('api.cities.index'))
            ->assertOk()
            ->assertJsonCount($cities->count(), 'data')
            ->assertJsonPath('data.0.id', $firstCity->id)
            ->assertJsonPath('data.0.name', $firstCity->name);
    }
}
