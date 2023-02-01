<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'type_id' => Contact::TYPE_ID_PHONE,
            'hotel_id' => Hotel::factory(),
            'value' => $this->faker->phoneNumber,
        ];
    }

    public function type(string $type): self
    {
        return $this->state(function () use ($type) {
            switch ($type) {
                case 'email':
                    $typeId = Contact::TYPE_ID_EMAIL;
                    $value = $this->faker->email;
                    break;
                case 'site':
                    $typeId = Contact::TYPE_ID_SITE;
                    $value = $this->faker->url;
                    break;
                case 'telegram':
                    $typeId = Contact::TYPE_ID_TELEGRAM;
                    $value = '@'. $this->faker->slug(1);
                    break;
                case 'vk':
                    $typeId = Contact::TYPE_ID_VK;
                    $value = '@'. $this->faker->slug(1);
                    break;
                case 'phone':
                default:
                    $typeId = Contact::TYPE_ID_PHONE;
                    $value = strval($this->faker->unique()->numberBetween(70000000001, 79999999999));
            }
            return [
                'type_id' => $typeId,
                'value' => $value,
            ];
        });
    }
}
