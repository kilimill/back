<?php

namespace Tests\Feature\HotelOwner;

use App\Models\Hotel;

trait HotelOwnerStepTwoTest
{
    /**
     * @dataProvider dataStepTwoValidations
     */
    public function testStepTwoValidationForCreate(int $code, array $data, array $errors): void
    {
        $response = $this->postJson(route('api.owner.hotels.upsert'), $data);

        $response->assertStatus($code)->assertJsonFragment([
            'errors' => $errors,
        ]);
    }

    public function dataStepTwoValidations(): array
    {
        return [
            'step_two_field_tags_is_array' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'tags' => 'string',
                ],
                [
                    'categories' => [
                        'tags' => [
                            'Значение поля Теги должно быть массивом.',
                        ],
                    ],
                ],
            ],
            'step_two_fields_in_tags_should_present_in_db' => [
                422,
                [
                    'status_id' => Hotel::STATUS_ID_DRAFT,
                    'tags' => [1,2,3],
                ],
                [
                    'categories' => [
                        'tags.0'=> [
                            'Выбранное значение для Тег некорректно.'
                        ],
                        'tags.1' => [
                            'Выбранное значение для Тег некорректно.'
                        ],
                        'tags.2' => [
                            'Выбранное значение для Тег некорректно.'
                        ],
                    ],
                ],
            ],
        ];
    }
}
