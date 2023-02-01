<?php

namespace Tests\Unit;

use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class UserAuthTest extends TestCase
{
    use DatabaseMigrations;

    public function testTwoUsersCanLoginInSameTime(): void
    {
        $user1 = User::factory()->asClient()->create();
        $user2 = User::factory()->asClient()->create();

        $this->assertDatabaseMissing((new Session())->getTable(), [
            'user_id' => $user1->getKey(),
        ]);
        $this->assertDatabaseMissing((new Session())->getTable(), [
            'user_id' => $user2->getKey(),
        ]);

        $this->postJson(route('auth.login'), ['phone' => $user1->phone]);
        $this->postJson(route('auth.login'), ['phone' => $user2->phone]);

        $this->postJson(route('auth.inputPrivateCode'), [
            'code' => '11111',
            'phone' => $user1->phone,
        ])->assertOk();

        $this->postJson(route('auth.inputPrivateCode'), [
            'code' => '11111',
            'phone' => $user2->phone,
        ])->assertOk();

        $this->assertDatabaseHas((new Session())->getTable(), [
            'user_id' => $user1->getKey(),
        ]);
        $this->assertDatabaseHas((new Session())->getTable(), [
            'user_id' => $user2->getKey(),
        ]);
    }
}
