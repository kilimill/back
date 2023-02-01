<?php

namespace Tests\Feature\HotelOwner;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HotelOwnerStepSixTest
{
    /**
     * @dataProvider dataStepSixValidations
     */
    public function testStepSixValidationForCreate(int $code, array $data, array $errors): void
    {
        Storage::fake('rooms');
        $response = $this->postJson(route('api.owner.hotels.upsert'), $data);

        $response->assertStatus($code)->assertJsonFragment([
            'errors' => $errors,
        ]);
    }

    public function dataStepSixValidations(): array
    {
        return [
            'step_six_field_rooms_is_array' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => 'string',
                ],
                [
                    'rooms' => [
                        'rooms' => [
                            'Значение поля Номера должно быть массивом.',
                        ],
                    ],
                ],
            ],
            'step_six_fields_in_rooms_required' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => [
                        [
                            'id' => '',
                            'name' => '',
                            'description' => '',
                            'guest_count' => '',
                            'meals_id' => '',
                            'quantity' => '',
                            'price' => '',
                            'price_weekend' => '',
                        ],
                    ],
                ],
                [
                    'rooms' => [
                        'rooms.0.id'=> [
                            'Поле rooms.0.id обязательно для заполнения.'
                        ],
                        'rooms.0.name'=> [
                            'Поле Название обязательно для заполнения.'
                        ],
                        'rooms.0.description'=> [
                            'Поле Описание обязательно для заполнения.'
                        ],
                        'rooms.0.guest_count'=> [
                            'Поле Количество гостей обязательно для заполнения.'
                        ],
                        'rooms.0.meals_id'=> [
                            'Поле Питание обязательно для заполнения.'
                        ],
                        'rooms.0.quantity'=> [
                            'Поле Количество обязательно для заполнения.'
                        ],
                        'rooms.0.price'=> [
                            'Поле Стоимость обязательно для заполнения.'
                        ],
                        'rooms.0.price_weekend'=> [
                            'Поле Стоимость на выходные обязательно для заполнения.'
                        ],
                    ],
                ],
            ],
            'step_six_room_id_exists_in_db' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => [
                        [
                            'id' => 1,
                            'name' => 'name',
                            'description' => 'description',
                            'guest_count' => 1,
                            'meals_id' => Room::MEALS_ID_1,
                            'quantity' => 1,
                            'price' => 123,
                            'price_weekend' => 123,
                            'media' => [
                                UploadedFile::fake()->image('room1.jpg')
                            ],
                        ],
                    ],
                ],
                [
                    'rooms' => [
                        'rooms.0.id' => [
                            'Выбранное значение для rooms.0.id некорректно.',
                        ],
                    ],
                ],
            ],
            'step_six_room_name_cannot_be_less_than_2_symbols' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => [
                        [
                            'name' => Str::random(1),
                            'description' => 'description',
                            'guest_count' => 1,
                            'meals_id' => Room::MEALS_ID_1,
                            'quantity' => 1,
                            'price' => 123,
                            'price_weekend' => 123,
                            'media' => [
                                UploadedFile::fake()->image('room1.jpg')
                            ],
                        ],
                    ],
                ],
                [
                    'rooms' => [
                        'rooms.0.name' => [
                            'Количество символов в поле Название должно быть не меньше 2.',
                        ],
                    ],
                ],
            ],
            'step_six_room_name_cannot_be_more_than_255_symbols' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => [
                        [
                            'name' => Str::random(256),
                            'description' => 'description',
                            'guest_count' => 1,
                            'meals_id' => Room::MEALS_ID_1,
                            'quantity' => 1,
                            'price' => 123,
                            'price_weekend' => 123,
                            'media' => [
                                UploadedFile::fake()->image('room1.jpg')
                            ],
                        ],
                    ],
                ],
                [
                    'rooms' => [
                        'rooms.0.name' => [
                            'Количество символов в поле Название не может превышать 255.',
                        ],
                    ],
                ],
            ],
            'step_six_room_description_cannot_be_less_than_10_symbols' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => [
                        [
                            'name' => 'name',
                            'description' => Str::random(9),
                            'guest_count' => 1,
                            'meals_id' => Room::MEALS_ID_1,
                            'quantity' => 1,
                            'price' => 123,
                            'price_weekend' => 123,
                            'media' => [
                                UploadedFile::fake()->image('room1.jpg')
                            ],
                        ],
                    ],
                ],
                [
                    'rooms' => [
                        'rooms.0.description' => [
                            'Количество символов в поле Описание должно быть не меньше 10.',
                        ],
                    ],
                ],
            ],
            'step_six_room_description_cannot_be_more_than_1000_symbols' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => [
                        [
                            'name' => 'name',
                            'description' => Str::random(1001),
                            'guest_count' => 1,
                            'meals_id' => Room::MEALS_ID_1,
                            'quantity' => 1,
                            'price' => 123,
                            'price_weekend' => 123,
                            'media' => [
                                UploadedFile::fake()->image('room1.jpg')
                            ],
                        ],
                    ],
                ],
                [
                    'rooms' => [
                        'rooms.0.description' => [
                            'Количество символов в поле Описание не может превышать 1000.',
                        ],
                    ],
                ],
            ],
            'step_six_room_guest_count_cannot_be_less_than_1' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => [
                        [
                            'name' => 'name',
                            'description' => 'description',
                            'guest_count' => 0,
                            'meals_id' => Room::MEALS_ID_1,
                            'quantity' => 1,
                            'price' => 123,
                            'price_weekend' => 123,
                            'media' => [
                                UploadedFile::fake()->image('room1.jpg')
                            ],

                        ],
                    ],
                ],
                [
                    'rooms' => [
                        'rooms.0.guest_count' => [
                            'Значение поля Количество гостей должно быть не меньше 1.',
                        ],
                    ],
                ],
            ],
            'step_six_room_guest_count_cannot_be_more_than_100' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => [
                        [
                            'name' => 'name',
                            'description' => 'description',
                            'guest_count' => 101,
                            'meals_id' => Room::MEALS_ID_1,
                            'quantity' => 1,
                            'price' => 123,
                            'price_weekend' => 123,
                            'media' => [
                                UploadedFile::fake()->image('room1.jpg')
                            ],
                        ],
                    ],
                ],
                [
                    'rooms' => [
                        'rooms.0.guest_count' => [
                            'Значение поля Количество гостей не может быть больше 100.',
                        ],
                    ],
                ],
            ],
            'step_six_room_meals_id_has_correct_value' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => [
                        [
                            'name' => 'name',
                            'description' => 'description',
                            'guest_count' => 1,
                            'meals_id' => collect(Room::MEALS_IDS)->keys()->last() + 1,
                            'quantity' => 1,
                            'price' => 123,
                            'price_weekend' => 123,
                            'media' => [
                                UploadedFile::fake()->image('room1.jpg')
                            ],
                        ],
                    ],
                ],
                [
                    'rooms' => [
                        'rooms.0.meals_id'=> [
                            'Выбранное значение для Питание некорректно.'
                        ],
                    ],
                ],
            ],
            'step_six_room_quantity_cannot_be_less_than_1' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => [
                        [
                            'name' => 'name',
                            'description' => 'description',
                            'guest_count' => 1,
                            'meals_id' => Room::MEALS_ID_1,
                            'quantity' => 0,
                            'price' => 123,
                            'price_weekend' => 123,
                            'media' => [
                                UploadedFile::fake()->image('room1.jpg')
                            ],
                        ],
                    ],
                ],
                [
                    'rooms' => [
                        'rooms.0.quantity' => [
                            'Значение поля Количество должно быть не меньше 1.',
                        ],
                    ],
                ],
            ],
            'step_six_room_quantity_cannot_be_more_than_100' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => [
                        [
                            'name' => 'name',
                            'description' => 'description',
                            'guest_count' => 1,
                            'meals_id' => Room::MEALS_ID_1,
                            'quantity' => 101,
                            'price' => 123,
                            'price_weekend' => 123,
                            'media' => [
                                UploadedFile::fake()->image('room1.jpg')
                            ],
                        ],
                    ],
                ],
                [
                    'rooms' => [
                        'rooms.0.quantity' => [
                            'Значение поля Количество не может быть больше 100.',
                        ],
                    ],
                ],
            ],
            'step_six_room_price_cannot_be_less_than_1' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => [
                        [
                            'name' => 'name',
                            'description' => 'description',
                            'guest_count' => 1,
                            'meals_id' => Room::MEALS_ID_1,
                            'quantity' => 1,
                            'price' => 0,
                            'price_weekend' => 123,
                            'media' => [
                                UploadedFile::fake()->image('room1.jpg')
                            ],
                        ],
                    ],
                ],
                [
                    'rooms' => [
                        'rooms.0.price' => [
                            'Значение поля Стоимость должно быть не меньше 1.',
                        ],
                    ],
                ],
            ],
            'step_six_room_price_cannot_be_more_than_1000000' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => [
                        [
                            'name' => 'name',
                            'description' => 'description',
                            'guest_count' => 1,
                            'meals_id' => Room::MEALS_ID_1,
                            'quantity' => 1,
                            'price' => 10000001,
                            'price_weekend' => 123,
                            'media' => [
                                UploadedFile::fake()->image('room1.jpg')
                            ],
                        ],
                    ],
                ],
                [
                    'rooms' => [
                        'rooms.0.price' => [
                            'Значение поля Стоимость не может быть больше 1000000.',
                        ],
                    ],
                ],
            ],
            'step_six_room_price_weekend_cannot_be_less_than_1' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => [
                        [
                            'name' => 'name',
                            'description' => 'description',
                            'guest_count' => 1,
                            'meals_id' => Room::MEALS_ID_1,
                            'quantity' => 1,
                            'price' => 123,
                            'price_weekend' => 0,
                            'media' => [
                                UploadedFile::fake()->image('room1.jpg')
                            ],
                        ],
                    ],
                ],
                [
                    'rooms' => [
                        'rooms.0.price_weekend' => [
                            'Значение поля Стоимость на выходные должно быть не меньше 1.',
                        ],
                    ],
                ],
            ],
            'step_six_room_price_weekend_cannot_be_more_than_1000000' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => [
                        [
                            'name' => 'name',
                            'description' => 'description',
                            'guest_count' => 1,
                            'meals_id' => Room::MEALS_ID_1,
                            'quantity' => 1,
                            'price' => 123,
                            'price_weekend' => 10000001,
                            'media' => [
                                UploadedFile::fake()->image('room1.jpg')
                            ],
                        ],
                    ],
                ],
                [
                    'rooms' => [
                        'rooms.0.price_weekend' => [
                            'Значение поля Стоимость на выходные не может быть больше 1000000.',
                        ],
                    ],
                ],
            ],
            'step_six_room_media_is_array' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'rooms' => [
                        [
                            'name' => 'name',
                            'description' => 'description',
                            'guest_count' => 1,
                            'meals_id' => Room::MEALS_ID_1,
                            'quantity' => 1,
                            'price' => 123,
                            'price_weekend' => 123,
                            'media' => 'string',
                        ],
                    ],
                ],
                [
                    'rooms' => [
                        'rooms.0.media' => [
                            'Значение поля Фотографии должно быть массивом.',
                        ],
                    ],
                ],
            ],
        ];
    }
}
