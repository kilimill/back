<?php

namespace Tests\Feature\HotelOwner;

use App\Models\Hotel;

trait HotelOwnerStepSevenTest
{
    /**
     * @dataProvider dataStepSevenValidations
     */
    public function testStepSevenValidationForCreate(int $code, array $data, array $errors): void
    {
        $response = $this->postJson(route('api.owner.hotels.upsert'), $data);

        $response->assertStatus($code)->assertJsonFragment([
            'errors' => $errors,
        ]);
    }

    public function dataStepSevenValidations(): array
    {
        return [
            'step_seven_field_lakes_is_array' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'lakes' => 'string',
                ],
                [
                    'lakes' => [
                        'lakes' => [
                            'Значение поля Ближайщие водоёмы должно быть массивом.',
                        ],
                    ],
                ],
            ],
            'step_seven_fields_in_lakes_required' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'lakes' => [
                        [
                            'id' => '',
                            'distance_shore' => '',
                        ],
                    ],
                ],
                [
                    'lakes' => [
                        'lakes.0.id'=> [
                            'Поле Река, море или озеро обязательно для заполнения.'
                        ],
                        'lakes.0.distance_shore'=> [
                            'Поле Удалённость от берега, м обязательно для заполнения.'
                        ],
                    ],
                ],
            ],
            'step_seven_room_lake_id_has_correct_value' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'lakes' => [
                        [
                            'id' => 1,
                            'distance_shore' => 100,
                        ],
                    ],
                ],
                [
                    'lakes' => [
                        'lakes.0.id'=> [
                            'Выбранное значение для Река, море или озеро некорректно.'
                        ],
                    ],
                ],
            ],
            'step_seven_room_distance_shore_cannot_be_less_than_1' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'lakes' => [
                        [
                            'id' => 1,
                            'distance_shore' => 0,
                        ],
                    ],
                ],
                [
                    'lakes' => [
                        'lakes.0.id'=> [
                            'Выбранное значение для Река, море или озеро некорректно.'
                        ],
                        'lakes.0.distance_shore' => [
                            'Значение поля Удалённость от берега, м должно быть не меньше 1.',
                        ],
                    ],
                ],
            ],
            'step_seven_room_distance_shore_cannot_be_more_than_10000' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'lakes' => [
                        [
                            'id' => 1,
                            'distance_shore' => 100001,
                        ],
                    ],
                ],
                [
                    'lakes' => [
                        'lakes.0.id'=> [
                            'Выбранное значение для Река, море или озеро некорректно.'
                        ],
                        'lakes.0.distance_shore' => [
                            'Значение поля Удалённость от берега, м не может быть больше 10000.',
                        ],
                    ],
                ],
            ],
        ];
    }
}
