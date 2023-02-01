<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city,
            'country_id' => Country::factory(),
            'region_id' => Region::factory(),
        ];
    }

    public function withName(string $name): self
    {
        return $this->state(function () use ($name) {
            return [
                'name' => $name,
            ];
        });
    }
}
