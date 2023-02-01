<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            'model_type' => get_class(new Hotel()),
            'model_id' => Hotel::factory(),
            'collection_name' => rand(1,2) ? 'hotels' : 'rooms',
            'name' => $this->faker->name,
            'file_name' => $this->faker->name . 'png',
            'mime_type' => 'png',
            'disk' => 'media',
            'conversions_disk' => 'media',
            'size' => 800000,
            'manipulations' => json_encode([]),
            'custom_properties' => json_encode(['preview' => false]),
            'generated_conversions' => json_encode([]),
            'responsive_images' => json_encode([]),
            'order_column' => 1,
        ];
    }

    public function asPreview(): self
    {
        return $this->state(function () {
            return [
                'custom_properties' => json_encode(['preview' => true]),
            ];
        });
    }
}
