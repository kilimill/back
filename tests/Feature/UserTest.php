<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Testing\File;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    private User $clientAuth;
    private string $clientExistingPhone;

    public function setUp(): void
    {
        parent::setUp();

        $this->clientAuth = User::factory()->asClient()->create();
        $this->userLogin($this->clientAuth);
    }

    public function dataInvalidUpdateRequest(): array
    {
        return [
            'name_not_str' => [
                ['name' => 123],
                ['name'],
            ],
            'name_too_small' => [
                ['name' => 'f'],
                ['name'],
            ],
            'name_not_long' => [
                ['name' => 'foo_bar_foo_bar_foo_bar_foo_bar'],
                ['name'],
            ],
            'name_not_exists' => [
                [],
                ['name'],
            ],
            'avatar_not_image' => [
                ['avatar' => 'foo'],
                ['avatar'],
            ],
            'phone_not_exists' => [
                [],
                ['phone'],
            ],
            'phone_not_string' => [
                ['phone' => 123],
                ['phone'],
            ],
            'phone_too_small' => [
                ['phone' => 'f'],
                ['phone'],
            ],
            'phone_too_long' => [
                ['phone' => 'foo_bar_foo_bar_foo_bar'],
                ['phone'],
            ],
            'phone_already_exists' => [
                ['phone' => '79997776655'],
                ['phone'],
            ],
            'email_not_exists' => [
                [],
                ['email'],
            ],
            'email_is_not_email' => [
                ['email' => 'test'],
                ['email'],
            ],
        ];
    }

    public function dataInvalidUpdatePartnerRequest(): array
    {
        return [
            'required' => [
                422,
                [
                    'first_name_owner' => '',
                    'last_name_owner' => '',
                    'phone_owner' => '',
                    'email_owner' => '',
                    'inn_owner' => '',
                    'legal_form_id' => '',
                    'accept' => '',
                    'agree' => '',
                ],
                [
                    "first_name_owner" => [
                        "Поле Имя партнера обязательно для заполнения."
                    ],
                    "last_name_owner" => [
                        "Поле Фамилия партнера обязательно для заполнения."
                    ],
                    "phone_owner" => [
                        "Поле Телефон партнера обязательно для заполнения."
                    ],
                    "email_owner" => [
                        "Поле Email партнера обязательно для заполнения."
                    ],
                    "inn_owner" => [
                        "Поле ИНН партнера обязательно для заполнения."
                    ],
                    "legal_form_id" => [
                        "Поле Организационно-правовая форма партнера обязательно для заполнения."
                    ],
                    "accept" => [
                        "Поле Принятие условий договора оферты обязательно для заполнения."
                    ],
                    "agree" => [
                        "Поле Согласие с политикой конфиденциальности обязательно для заполнения."
                    ]
                ],
            ],
            'invalid_type_and_min_symbols' => [
                422,
                [
                    'first_name_owner' => 'a',
                    'last_name_owner' => 'a',
                    'phone_owner' => 123,
                    'email_owner' => '123',
                    'inn_owner' => '123',
                    'legal_form_id' => 'foo',
                    'accept' => 'no',
                    'agree' => 'no',
                ],
                [
                    "first_name_owner" => [
                        "Количество символов в поле Имя партнера должно быть не меньше 2."
                    ],
                    "last_name_owner" => [
                        "Количество символов в поле Фамилия партнера должно быть не меньше 2."
                    ],
                    "phone_owner" => [
                        "Поле Телефон партнера имеет неправильный формат."
                    ],
                    "email_owner" => [
                        "Значение поля Email партнера должно быть действительным электронным адресом."
                    ],
                    "inn_owner" => [
                        "Количество символов в поле ИНН партнера должно быть не меньше 10."
                    ],
                    "legal_form_id" => [
                        "Значение поля Организационно-правовая форма партнера должно быть целым числом.",
                        "Выбранное значение для Организационно-правовая форма партнера некорректно."
                    ],
                    "accept" => [
                        "Вы должны принять Принятие условий договора оферты."
                    ],
                    "agree" => [
                        "Вы должны принять Согласие с политикой конфиденциальности."
                    ]
                ],
            ],
            'max_symbols' => [
                422,
                [
                    'first_name_owner' => 'ФёдорФёдорФёдорФёдорФёдорФёдорФёдор',
                    'last_name_owner' => 'НестеровНестеровНестеровНестеровНестеров',
                    'phone_owner' => '77888888888',
                    'email_owner' => 'test@test.com',
                    'inn_owner' => '1234567890129',
                    'kpp_owner' => '1234567899',
                    'legal_form_id' => 1,
                    'accept' => 'on',
                    'agree' => 'on',
                ],
                [
                    "first_name_owner" => [
                        "Количество символов в поле Имя партнера не может превышать 30."
                    ],
                    "last_name_owner" => [
                        "Количество символов в поле Фамилия партнера не может превышать 30."
                    ],
                    "inn_owner" => [
                        "Количество символов в поле ИНН партнера не может превышать 12."
                    ],
                    "kpp_owner" => [
                        "Количество символов в поле КПП партнера не может превышать 9."
                    ]
                ],
            ],
        ];

    }

    public function dataInvalidChangePhoneRequest(): array
    {
        return [
            'code_not_exists' => [
                [],
                ['code'],
            ],
            'code_not_string' => [
                ['code' => 123123123],
                ['code'],
            ],
        ];
    }

    public function testIndexUser(): void
    {
        $response = $this->getJson(route('api.profile.index'))->assertOk();
        $response->assertExactJson([
            'data' => [
                'id' => $this->clientAuth->getKey(),
                'name' => $this->clientAuth->name,
                'email' => $this->clientAuth->email,
                'avatar' => $this->clientAuth->getMedia('avatars')->pluck('original_url')->first() ?? null,
                'phone' => $this->clientAuth->phone,
                'role_id' => $this->clientAuth->role_id,
                'first_name_owner' => $this->clientAuth->first_name_owner,
                'last_name_owner' => $this->clientAuth->last_name_owner,
                'phone_owner' => $this->clientAuth->phone_owner,
                'email_owner' => $this->clientAuth->email_owner,
                'inn_owner' => $this->clientAuth->inn_owner,
                'kpp_owner' => $this->clientAuth->kpp_owner,
                'legal_form_id' => $this->clientAuth->legal_form_id,
            ],
        ]);
    }

    public function testIndexUserNotAuth(): void
    {
        $this->userLogOut();
        $this->getJson(route('api.profile.index'))->assertUnauthorized();
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function testUpdateUser(): void
    {
        $data = [
            'name' => 'Фёдор',
            'email' => 'fedor@gmail.com',
            'avatar' => File::image('avatar.jpg'),
            'phone' => $this->clientAuth->phone,
        ];

        $this->postJson(route('api.profile.update'), $data)
            ->assertOk()
            ->assertJsonPath('data.id', $this->clientAuth->getKey())
            ->assertJsonPath('data.name', $data['name'])
            ->assertJsonPath('data.email', $data['email']);

        $media = $this->clientAuth->getMedia('avatars');
        $this->assertFileExists($media->first()->getPath());
        $this->assertCount(1, $media);
        $this->assertDatabaseHas((new Media())->getTable(), [
            'id' => $media->first()->getKey(),
            'model_type' => 'App\\Models\\User',
        ]);
    }

    public function testUpdateUserNotAuth(): void
    {
        $this->userLogOut();
        $this->postJson(route('api.profile.update'))->assertUnauthorized();
    }

    public function testUpdateUserPartner(): void
    {
        $data = [
            'first_name_owner' => 'Фёдор',
            'last_name_owner' => 'Нестеров',
            'phone_owner' => $this->clientAuth->phone,
            'email_owner' => $this->clientAuth->email,
            'inn_owner' => '123456789012',
            'kpp_owner' => '123456789',
            'legal_form_id' => User::LEGAL_FORM_ID_ENTERPRISER,
            'accept' => 'on',
            'agree' => 'on',
        ];

        $this->postJson(route('api.profile.updatePartner'), $data)
            ->assertOk()
            ->assertJsonPath('data.id', $this->clientAuth->getKey())
            ->assertJsonPath('data.first_name_owner', $data['first_name_owner'])
            ->assertJsonPath('data.last_name_owner', $data['last_name_owner'])
            ->assertJsonPath('data.email_owner', $data['email_owner'])
            ->assertJsonPath('data.phone_owner', $data['phone_owner'])
            ->assertJsonPath('data.inn_owner', $data['inn_owner'])
            ->assertJsonPath('data.kpp_owner', $data['kpp_owner'])
            ->assertJsonPath('data.legal_form_id', $data['legal_form_id']);
    }

    public function testUpdateUserPartnerNotAuth(): void
    {
        $this->userLogOut();
        $this->postJson(route('api.profile.updatePartner'))->assertUnauthorized();
    }

    /**
     * @dataProvider dataInvalidUpdateRequest
     */
    public function testUserValidation(array $requestData): void
    {
        User::factory()->asClient()->create(['phone' => '79997776655']);

        $this->postJson(route('api.profile.update'), $requestData)->assertUnprocessable();
    }

    /**
     * @dataProvider dataInvalidUpdatePartnerRequest
     */
    public function testUserPartnerValidation(int $code, array $data, array $errors): void
    {
        User::factory()->asClient()->create(['phone' => '79997776655']);

        $this->postJson(route('api.profile.updatePartner'), $data)
            ->assertStatus($code)
            ->assertJsonFragment([
                'errors' => $errors,
            ]);


    }

    public function testUserCanChangePhone(): void
    {
        $data = [
            'name' => $this->clientAuth->name,
            'phone' => '73235517777',
            'email' => $this->clientAuth->email,
        ];

        $this->postJson(route('api.profile.update'), $data);

        $this->postJson(route('api.profile.inputChangePhoneCode'), [
            'code' => '11111',
            'phone' => $data['phone'],
        ]);

        $this->assertDatabaseHas((new User)->getTable(), [
            'id' => $this->clientAuth->getKey(),
            'phone' => $data['phone'],
        ]);
    }

    public function testUserCanNotUpdateProfileWithWrongCode(): void
    {
        $phone = '73235517777';
        $this->postJson(route('api.profile.update', ['phone' => $phone]));

        $response = $this->postJson(route('api.profile.inputChangePhoneCode'), [
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

    public function testUserCanNotUpdateProfileWithWrongPhone(): void
    {
        $phone = '73235517777';
        $this->postJson(route('api.profile.update', ['phone' => $phone]));

        $response = $this->postJson(route('api.profile.inputChangePhoneCode'), [
            'code' => '11111',
            'phone' => 'wrong phone',
            'email' => $this->clientAuth->email,
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

    public function testChangePhoneNotAuth()
    {
        $this->userLogOut();
        $this->postJson(route('api.profile.inputChangePhoneCode'), [
            'code' => '11111',
            'phone' => '78883338888',
        ])->assertUnauthorized();
    }

    /**
     * @dataProvider dataInvalidChangePhoneRequest
     */
    public function testChangePhoneValidation(array $requestData)
    {
        $this->postJson(route('api.profile.update'), [
            'phone' => '73235517777',
            'email' => $this->clientAuth->email,
        ]);
        $this->postJson(route('api.profile.inputChangePhoneCode'), $requestData)->assertUnprocessable();
    }

    public function testUserCanChangeEmail(): void
    {
        $data = [
            'name' => $this->clientAuth->name,
            'phone' => $this->clientAuth->phone,
            'email' => 'test@test.test',
        ];

        $this->assertDatabaseMissing((new User())->getTable(), [
            'id' => $this->clientAuth->getKey(),
            'email' => $data['email'],
        ]);

        $response = $this->postJson(route('api.profile.update'), $data)->assertOk();

        $this->assertDatabaseHas((new User)->getTable(), [
            'id' => $this->clientAuth->getKey(),
            'email' => $data['email'],
        ]);

        $response->assertExactJson([
            'message' => 'Данные успешно сохранены.',
            'data' => [
                'id' => $this->clientAuth->getKey(),
                'name' => $data['name'],
                'email' => $data['email'],
                'avatar' => $this->clientAuth->getMedia('avatars')->pluck('original_url')->first() ?? null,
                'phone' => $data['phone'],
                'role_id' => $this->clientAuth->role_id,
                'first_name_owner' => $this->clientAuth->first_name_owner,
                'last_name_owner' => $this->clientAuth->last_name_owner,
                'phone_owner' => $this->clientAuth->phone_owner,
                'email_owner' => $this->clientAuth->email_owner,
                'inn_owner' => $this->clientAuth->inn_owner,
                'kpp_owner' => $this->clientAuth->kpp_owner,
                'legal_form_id' => $this->clientAuth->legal_form_id,
            ],
        ]);
    }

    public function testUserCanSendOwnEmailAndPhoneNoErrors(): void
    {
        $response = $this->postJson(route('api.profile.update'), [
            'name' => $this->clientAuth->name,
            'phone' => $this->clientAuth->phone,
            'email' => $this->clientAuth->email,
        ]);

        $response->assertOk();

        $response->assertExactJson([
            'message' => 'Данные успешно сохранены.',
            'data' => [
                'id' => $this->clientAuth->getKey(),
                'name' => $this->clientAuth->name,
                'email' => $this->clientAuth->email,
                'avatar' => $this->clientAuth->getMedia('avatars')->pluck('original_url')->first() ?? null,
                'phone' => $this->clientAuth->phone,
                'role_id' => $this->clientAuth->role_id,
                'first_name_owner' => $this->clientAuth->first_name_owner,
                'last_name_owner' => $this->clientAuth->last_name_owner,
                'phone_owner' => $this->clientAuth->phone_owner,
                'email_owner' => $this->clientAuth->email_owner,
                'inn_owner' => $this->clientAuth->inn_owner,
                'kpp_owner' => $this->clientAuth->kpp_owner,
                'legal_form_id' => $this->clientAuth->legal_form_id,
            ],
        ]);
    }
}
