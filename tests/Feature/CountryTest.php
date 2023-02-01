<?php

namespace Tests\Feature;

use App\Models\Country;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CountryTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndexCountries(): void
    {
        $countries = Country::factory(10)->create();

        /** @var Country $firstCountry */
        $firstCountry = $countries->first();

        $this->getJson(route('api.countries.index'))
            ->assertOk()
            ->assertJsonCount($countries->count(), 'data')
            ->assertJsonPath('data.0.id', $firstCountry->id)
            ->assertJsonPath('data.0.name', $firstCountry->name);
    }
}
