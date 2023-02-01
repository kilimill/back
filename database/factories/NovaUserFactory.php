<?php

namespace Database\Factories;

use App\Models\NovaUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class NovaUserFactory extends Factory
{
    protected $model = NovaUser::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => strval($this->faker->unique()->numberBetween(70000000001, 79999999999)),
            'email_verified_at' => now(),
            'password' => bcrypt('123456'),
            'remember_token' => Str::random(10),
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(function (NovaUser $user) {
            $directory = storage_path('seeds/avatars');
            $files = File::files($directory);

            $user->addMedia($files[rand(0,2)])->preservingOriginal()->toMediaCollection('avatars');
        });
    }
}
