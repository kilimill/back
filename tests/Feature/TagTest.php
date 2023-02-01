<?php

namespace Tests\Feature;

use App\Models\Tag;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TagTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndexTags(): void
    {
        $tags = Tag::factory(10)->create();

        /** @var Tag $firstTag */
        $firstTag = $tags->first();

        $this->getJson(route('api.tags.index'))
            ->assertOk()
            ->assertJsonCount($tags->count(), 'data')
            ->assertJsonPath('data.0.id', $firstTag->id)
            ->assertJsonPath('data.0.name', $firstTag->name)
            ->assertJsonPath('data.0.icon', $firstTag->icon);
    }
}
