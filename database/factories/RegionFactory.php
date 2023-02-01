<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegionFactory extends Factory
{
    protected $model = Region::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->word) . ' ' . 'Republic',
            'country_id' => Country::factory(),
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
