<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    public function testNewUserCanCreateAccountWithPhone(): void
    {
        $phone = '78888888888';

        $this->assertCount(0, User::all());

        $this->postJson(route('auth.login'), ['phone' => $phone]);
        $response = $this->postJson(route('auth.inputPrivateCode'), [
            'code' => '11111',
            'phone' => $phone,
        ]);
        $response->assertOk();

        $this->assertCount(1, User::all());
        $this->assertDatabaseHas((new User())->getTable(), [
            'phone' => $phone,
        ]);
    }

    public function testExistedUserCanLoginWithPhone(): void
    {
        $phone = '78888888888';
        $user = User::factory()->withPhone($phone)->create();

        $this->assertCount(1, User::all());

        $this->postJson(route('auth.login'), ['phone' => $user->phone]);
        $response = $this->postJson(route('auth.inputPrivateCode'), [
            'code' => '11111',
            'phone' => $phone,
        ]);
        $response->assertOk();

        $this->assertCount(1, User::all());
    }

    public function testNewUserCanNotCreateAccountWithOutPhone(): void
    {
        $this->assertCount(0, User::all());

        $response = $this->postJson(route('auth.login'), ['phone' => '']);
        $response->assertUnprocessable();
        $response->assertJsonFragment([
            'errors' => [
                'phone' => [
                    'Поле Телефон обязательно для заполнения.',
                ],
            ],
        ]);
        $this->assertCount(0, User::all());
    }

    public function testUserCanNotLoginWithOutCode(): void
    {
        $phone = '78888888888';
        $user = User::factory()->withPhone($phone)->create();

        $this->postJson(route('auth.login'), ['phone' => $user->phone]);
        $response = $this->postJson(route('auth.inputPrivateCode'), [
            'code' => '',
            'phone' => $phone,
        ]);
        $response->assertUnprocessable();
        $response->assertJsonFragment([
            'errors' => [
                'code' => [
                    'Поле Код обязательно для заполнения.',
                ],
            ],
        ]);
    }

    public function testUserCanNotLoginWithOutPhone(): void
    {
        $phone = '78888888888';
        $user = User::factory()->withPhone($phone)->create();

        $this->postJson(route('auth.login'), ['phone' => $user->phone]);
        $response = $this->postJson(route('auth.inputPrivateCode'), [
            'code' => '11111',
            'phone' => '',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonFragment([
            'errors' => [
                'phone' => [
                    'Поле Телефон обязательно для заполнения.',
                ],
            ],
        ]);
    }

    public function testUserCanNotLoginWithWrongCode(): void
    {
        $phone = '78888888888';
        $user = User::factory()->withPhone($phone)->create();

        $this->postJson(route('auth.login'), ['phone' => $user->phone]);
        $response = $this->postJson(route('auth.inputPrivateCode'), [
            'code' => 'wrong code',
            'phone' => $phone,
        ]);
        $response->assertUnprocessable();
        $response->assertJsonFragment([
            'errors' => [
                'code' => [
                    'Код не подходит или срок его действия истек.',
                ],
            ],
        ]);
    }

    public function testUserCanNotLoginWithWrongPhone(): void
    {
        $this->postJson(route('auth.login'), ['phone' => '88888888888']);
        $response = $this->postJson(route('auth.inputPrivateCode'), [
            'code' => '11111',
            'phone' => 'wrong phone',
        ]);
        $response->assertUnprocessable();
        $response->assertJsonFragment([
            'errors' => [
                'phone' => [
                    'Поле Телефон имеет неправильный формат.',
                ],
            ],
        ]);
    }
}
