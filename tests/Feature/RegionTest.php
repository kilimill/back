<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Region;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class RegionTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndexRegion(): void
    {
        $country = Country::factory()->create(['name' => 'Россия']);
        $regions = Region::factory(10)->for($country)->create();

        /** @var Region $firstRegion */
        $firstRegion = $regions->first();

        $this->getJson(route('api.regions.index'))
            ->assertOk()
            ->assertJsonCount($regions->count(), 'data')
            ->assertJsonPath('data.0.id', $firstRegion->id)
            ->assertJsonPath('data.0.name', $firstRegion->name);
    }
}
