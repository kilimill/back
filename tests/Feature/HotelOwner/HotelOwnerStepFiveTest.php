<?php

namespace Tests\Feature\HotelOwner;

use App\Models\Hotel;
use Illuminate\Support\Str;

trait HotelOwnerStepFiveTest
{
    /**
     * @dataProvider dataStepFiveValidations
     */
    public function testStepFiveValidationForCreate(int $code, array $data, array $errors): void
    {
        $response = $this->postJson(route('api.owner.hotels.upsert'), $data);

        $response->assertStatus($code)->assertJsonFragment([
            'errors' => $errors,
        ]);
    }

    public function dataStepFiveValidations(): array
    {
        return [
            'step_five_fields_required' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'address' => '',
                    'coordinates' => '',
//                    'distance_city' => '',
                ],
                [
                    'address' => [
                        'coordinates'=> [
                            'Поле Координаты обязательно для заполнения.'
                        ],
                        'address'=> [
                            'Поле Адрес обязательно для заполнения.'
                        ],
//                        'distance_city'=> [
//                            'Поле Расстояние до города обязательно для заполнения.'
//                        ],
                    ],
                ],
            ],
            'step_five_address_country_value_required' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'address' => json_encode([
                        [
                            'kind' => 'province',
                            'name' => Str::random(10),
                        ],
                        [
                            'kind' => 'locality',
                            'name' => Str::random(10),
                        ],
                    ]),
                ],
                [
                    'address' => [
                        'address' => [
                            'Поле Адрес имеет неправильный формат.',
                        ],
                        'coordinates'=> [
                            'Поле Координаты обязательно для заполнения.'
                        ],
                    ],
                ],
            ],
            'step_five_address_country_value_should_be_russia' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'address' => json_encode([
                        [
                            'kind' => 'country',
                            'name' => Str::random(10),
                        ],
                        [
                            'kind' => 'province',
                            'name' => Str::random(10),
                        ],
                        [
                            'kind' => 'locality',
                            'name' => Str::random(10),
                        ],
                    ]),
                ],
                [
                    'address' => [
                        'address' => [
                            'Поле Адрес имеет неправильный формат.',
                        ],
                        'coordinates'=> [
                            'Поле Координаты обязательно для заполнения.'
                        ],
                    ],
                ],
            ],
            'step_five_address_province_value_required' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'address' => json_encode([
                        [
                            'kind' => 'country',
                            'name' => Str::random(10),
                        ],
                        [
                            'kind' => 'locality',
                            'name' => Str::random(10),
                        ],
                    ]),
                ],
                [
                    'address' => [
                        'address' => [
                            'Поле Адрес имеет неправильный формат.',
                        ],
                        'coordinates'=> [
                            'Поле Координаты обязательно для заполнения.'
                        ],
                    ],
                ],
            ],
            'step_five_address_locality_value_required' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'address' => json_encode([
                        [
                            'kind' => 'country',
                            'name' => Str::random(10),
                        ],
                        [
                            'kind' => 'locality',
                            'name' => Str::random(10),
                        ],
                    ]),
                ],
                [
                    'address' => [
                        'address' => [
                            'Поле Адрес имеет неправильный формат.',
                        ],
                        'coordinates'=> [
                            'Поле Координаты обязательно для заполнения.'
                        ],
                    ],
                ],
            ],
//            'step_five_distance_city_cannot_be_less_than_0' => [
//                422,
//                [
//                    'status_id' => Hotel::STATUS_ID_DRAFT,
//                    'distance_city' => -1,
//                ],
//                [
//                    'address' => [
//                        'distance_city' => [
//                            'Значение поля Расстояние до города должно быть не меньше 0.',
//                        ],
//                    ],
//                ],
//            ],
//            'step_five_distance_city_cannot_be_more_than_1000000' => [
//                422,
//                [
//                    'status_id' => Hotel::STATUS_ID_DRAFT,
//                    'distance_city' => 10000001,
//                ],
//                [
//                    'address' => [
//                        'distance_city' => [
//                            'Значение поля Расстояние до города не может быть больше 1000000.',
//                        ],
//                    ],
//                ],
//            ],
            'step_five_coordinates_value_should_be_correct' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'coordinates' => Str::random(4),
                ],
                [
                    'address' => [
                        'coordinates' => [
                            'Значение поля Координаты должно быть массивом.',
                            'Количество элементов в поле Координаты должно быть равным 2.',
                        ],
                    ],
                ],
            ],
        ];
    }
}
