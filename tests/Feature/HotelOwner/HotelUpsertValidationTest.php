<?php

namespace Tests\Feature\HotelOwner;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class HotelUpsertValidationTest extends TestCase
{
    use DatabaseMigrations;

    use HotelOwnerStepOneTest,
        HotelOwnerStepTwoTest,
        HotelOwnerStepThreeTest,
        HotelOwnerStepFourTest,
        HotelOwnerStepFiveTest,
        HotelOwnerStepSixTest,
        HotelOwnerStepSevenTest,
        HotelOwnerStepEightTest;

    private string $token;

    public function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->asClient()->create();
        $this->userLogin($user);
    }

    /**
     * @dataProvider dataStatusIdValidations
     */
    public function testStatusIdValidation(int $code, array $data, array $errors): void
    {
        $response = $this->postJson(route('api.owner.hotels.upsert'), $data);

        $response->assertStatus($code)->assertJsonFragment([
            'errors' => $errors,
        ]);
    }

    public function dataStatusIdValidations(): array
    {
        return [
            'status_id_required' => [
                422,
                [],
                [
                    'extra' => [
                        'status_id' => [
                            'Поле status id обязательно для заполнения.',
                        ]
                    ],
                ],
            ],
            'status_id_integer' => [
                422,
                ['status_id' => 'string'],
                [
                    'extra' => [
                        'status_id' => [
                            'Значение поля status id должно быть целым числом.',
                            'Выбранное значение для status id некорректно.',
                        ],
                    ],
                ],
            ],
            'status_id_does_not_accept_active' => [
                422,
                ['status_id' => Hotel::STATUS_ID_ACTIVE],
                [
                    'extra' => [
                        'status_id' => [
                            'Выбранное значение для status id некорректно.',
                        ],
                    ],
                ],
            ],
        ];
    }
}
