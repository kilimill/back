<?php

namespace Tests\Feature\HotelOwner;

use App\Models\Hotel;
use Illuminate\Support\Str;

trait HotelOwnerStepOneTest
{
    /**
     * @dataProvider dataStepOneValidations
     */
    public function testStepOneValidationForCreate(int $code, array $data, array $errors): void
    {
        $response = $this->postJson(route('api.owner.hotels.upsert'), $data);

        $response->assertStatus($code)->assertJsonFragment([
            'errors' => $errors,
        ]);
    }

    public function dataStepOneValidations(): array
    {
        return [
            'step_one_fields_required' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'name' => '',
                    'type_id' => '',
                    'description' => '',
                ],
                [
                    'index' => [
                        'name' => [
                            'Поле Название обязательно для заполнения.',
                        ],
                        'description' => [
                            'Поле Описание обязательно для заполнения.',
                        ],
                        'type_id' => [
                            'Поле Тип отеля обязательно для заполнения.',
                        ],
                    ],
                ],
            ],
            'step_one_type_id_has_correct_value' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'type_id' => collect(Hotel::TYPE_IDS)->keys()->last() + 1,
                ],
                [
                    'index' => [
                        'type_id' => [
                            'Выбранное значение для Тип отеля некорректно.',
                        ],
                    ],
                ],
            ],
            'step_one_name_cannot_be_less_than_2_symbols' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'name' => Str::random(1),
                ],
                [
                    'index' => [
                        'name' => [
                            'Количество символов в поле Название должно быть не меньше 2.',
                        ],
                    ],
                ],
            ],
            'step_one_name_cannot_be_more_than_255_symbols' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'name' => Str::random(256),
                ],
                [
                    'index' => [
                        'name' => [
                            'Количество символов в поле Название не может превышать 255.',
                        ],
                    ],
                ],
            ],
            'step_one_description_cannot_be_less_than_5_symbols' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'description' => Str::random(4),
                ],
                [
                    'index' => [
                        'description' => [
                            'Количество символов в поле Описание должно быть не меньше 5.',
                        ],
                    ],
                ],
            ],
            'step_one_description_cannot_be_more_than_2000_symbols' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'description' => Str::random(2001),
                ],
                [
                    'index' => [
                        'description' => [
                            'Количество символов в поле Описание не может превышать 2000.',
                        ],
                    ],
                ],
            ],
        ];
    }
}
