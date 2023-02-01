<?php

namespace Tests\Feature;

use App\Models\Room;
use Tests\TestCase;

class MealsTest extends TestCase
{
    public function testIndexMeals(): void
    {
        $this->getJson(route('api.meals.index'))
            ->assertOk()
            ->assertJsonPath('data.0.id', Room::MEALS_ID_1)
            ->assertJsonPath('data.0.name', Room::MEALS_IDS[1]);
    }
}
