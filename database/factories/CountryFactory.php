<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->country,
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
