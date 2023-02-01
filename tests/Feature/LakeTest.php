<?php

namespace Tests\Feature;

use App\Models\Lake;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class LakeTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndexLakes(): void
    {
        $lakes = Lake::factory(10)->create();

        /** @var Lake $firstLake */
        $firstLake = $lakes->first();

        $this->getJson(route('api.lakes.index'))
            ->assertOk()
            ->assertJsonCount($lakes->count(), 'data')
            ->assertJsonPath('data.0.id', $firstLake->id)
            ->assertJsonPath('data.0.name', $firstLake->name);

    }
}
