<?php

namespace Database\Factories;

use App\Models\Lake;
use Illuminate\Database\Eloquent\Factories\Factory;

class LakeFactory extends Factory
{
    protected $model = Lake::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->slug(1)) . ' '. 'lake',
        ];
    }
}
