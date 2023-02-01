<?php

namespace Tests\Feature\HotelOwner;

use App\Models\Hotel;
use Illuminate\Support\Str;

trait HotelOwnerStepEightTest
{
    /**
     * @dataProvider dataStepEightValidations
     */
    public function testStepEightValidationForCreate(int $code, array $data, array $errors): void
    {
        $response = $this->postJson(route('api.owner.hotels.upsert'), $data);

        $response->assertStatus($code)->assertJsonFragment([
            'errors' => $errors,
        ]);
    }

    public function dataStepEightValidations(): array
    {
        return [
            'step_eight_fields_in_rooms_required' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'conditions' => '',
                    'detailed_route' => '',
                    'season_id' => '',
                    'min_days' => '',
                    'check_in_hour' => '',
                    'check_out_hour' => '',
                ],
                [
                    'info' => [
                        'conditions'=> [
                            'Поле Условия обязательно для заполнения.'
                        ],
                        'detailed_route'=> [
                            'Поле Подробный маршрут обязательно для заполнения.'
                        ],
                        'season_id'=> [
                            'Поле Сезон обязательно для заполнения.'
                        ],
                        'min_days'=> [
                            'Поле Минимальное количество дней обязательно для заполнения.'
                        ],
                        'check_in_hour'=> [
                            'Поле Час заезда обязательно для заполнения.'
                        ],
                        'check_out_hour'=> [
                            'Поле Час выезда обязательно для заполнения.'
                        ],
                    ],
                ],
            ],
            'step_eight_conditions_cannot_be_less_than_5_symbols' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'conditions' => Str::random(4),
                ],
                [
                    'info' => [
                        'conditions' => [
                            'Количество символов в поле Условия должно быть не меньше 5.',
                        ],
                    ],
                ],
            ],
            'step_eight_conditions_cannot_be_more_than_2000_symbols' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'conditions' => Str::random(2001),
                ],
                [
                    'info' => [
                        'conditions' => [
                            'Количество символов в поле Условия не может превышать 2000.',
                        ],
                    ],
                ],
            ],
            'step_eight_detailed_route_cannot_be_less_than_5_symbols' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'detailed_route' => Str::random(4),
                ],
                [
                    'info' => [
                        'detailed_route' => [
                            'Количество символов в поле Подробный маршрут должно быть не меньше 5.',
                        ],
                    ],
                ],
            ],
            'step_eight_detailed_route_cannot_be_more_than_2000_symbols' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'detailed_route' => Str::random(2001),
                ],
                [
                    'info' => [
                        'detailed_route' => [
                            'Количество символов в поле Подробный маршрут не может превышать 2000.',
                        ],
                    ],
                ],
            ],
            'step_eight_season_id_has_correct_value' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'season_id' => collect(Hotel::SEASON_IDS)->keys()->last() + 1,
                ],
                [
                    'info' => [
                        'season_id'=> [
                            'Выбранное значение для Сезон некорректно.'
                        ],
                    ],
                ],
            ],
            'step_eight_min_days_cannot_be_less_than_1' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'min_days' => 0,
                ],
                [
                    'info' => [
                        'min_days' => [
                            'Значение поля Минимальное количество дней должно быть не меньше 1.',
                        ],
                    ],
                ],
            ],
            'step_eight_min_days_cannot_be_more_than_60' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'min_days' => 61,
                ],
                [
                    'info' => [
                        'min_days' => [
                            'Значение поля Минимальное количество дней не может быть больше 60.',
                        ],
                    ],
                ],
            ],
            'step_eight_check_in_hour_cannot_be_less_than_1' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'check_in_hour' => -1,
                ],
                [
                    'info' => [
                        'check_in_hour' => [
                            'Значение поля Час заезда должно быть не меньше 0.',
                        ],
                    ],
                ],
            ],
            'step_eight_check_in_hour_cannot_be_more_than_60' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'check_in_hour' => 24,
                ],
                [
                    'info' => [
                        'check_in_hour' => [
                            'Значение поля Час заезда не может быть больше 23.',
                        ],
                    ],
                ],
            ],
            'step_eight_check_out_hour_cannot_be_less_than_1' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'check_out_hour' => -1,
                ],
                [
                    'info' => [
                        'check_out_hour' => [
                            'Значение поля Час выезда должно быть не меньше 0.',
                        ],
                    ],
                ],
            ],
            'step_eight_check_out_hour_cannot_be_more_than_60' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'check_out_hour' => 24,
                ],
                [
                    'info' => [
                        'check_out_hour' => [
                            'Значение поля Час выезда не может быть больше 23.',
                        ],
                    ],
                ],
            ],
        ];
    }
}
