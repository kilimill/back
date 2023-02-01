<?php

namespace Tests\Feature\HotelOwner;

use App\Models\Contact;
use App\Models\Hotel;

trait HotelOwnerStepFourTest
{
    /**
     * @dataProvider dataStepFourValidations
     */
    public function testStepFourValidationForCreate(int $code, array $data, array $errors): void
    {
        $response = $this->postJson(route('api.owner.hotels.upsert'), $data);

        $response->assertStatus($code)->assertJsonFragment([
            'errors' => $errors,
        ]);
    }

    public function dataStepFourValidations(): array
    {
        return [
            'step_four_field_contacts_is_array' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'contacts' => 'string',
                ],
                [
                    'contacts' => [
                        'contacts' => [
                            'Значение поля Контанты должно быть массивом.',
                        ],
                    ],
                ],
            ],
            'step_four_field_id_in_contacts_has_correct_value' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'contacts' => [
                        [
                            'id' => 1,
                            'type_id' => Contact::TYPE_ID_EMAIL,
                            'value' => 'test@test.test',
                        ],
                    ],
                ],
                [
                    'contacts' => [
                        'contacts.0.id'=> [
                            'Выбранное значение для contacts.0.id некорректно.'
                        ],
                    ],
                ],
            ],
            'step_four_fields_in_contacts_required' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'contacts' => [
                        [
                            'type_id' => '',
                            'value' => '',
                        ],
                    ],
                ],
                [
                    'contacts' => [
                        'contacts.0.type_id'=> [
                            'Поле Тип обязательно для заполнения.'
                        ],
                        'contacts.0.value'=> [
                            'Поле Контакт обязательно для заполнения.'
                        ],
                    ],
                ],
            ],
            'step_four_field_type_id_in_contacts_has_correct_value' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'contacts' => [
                        [
                            'type_id' => collect(Contact::TYPE_IDS)->keys()->last() + 1,
                            'value' => 'test value contact',
                        ],
                    ],
                ],
                [
                    'contacts' => [
                        'contacts.0.type_id'=> [
                            'Выбранное значение для Тип некорректно.'
                        ],
                        'contacts.0.value'=> [
                            'Поле Контакт имеет неправильный формат.'
                        ],
                    ],
                ],
            ],
            'step_four_field_value_in_contacts_has_correct_value_for_phone' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'contacts' => [
                        [
                            'type_id' => Contact::TYPE_ID_PHONE,
                            'value' => 'test value phone',
                        ],
                    ],
                ],
                [
                    'contacts' => [
                        'contacts.0.value'=> [
                            'Поле Контакт имеет неправильный формат.'
                        ],
                    ],
                ],
            ],
            'step_four_field_value_in_contacts_has_correct_value_for_email' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'contacts' => [
                        [
                            'type_id' => Contact::TYPE_ID_EMAIL,
                            'value' => 'test value email',
                        ],
                    ],
                ],
                [
                    'contacts' => [
                        'contacts.0.value'=> [
                            'Поле Контакт имеет неправильный формат.'
                        ],
                    ],
                ],
            ],
        ];
    }
}
