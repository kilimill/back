<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'role_id' => $this->faker->randomKey(User::ROLE_IDS),
            'phone' => strval($this->faker->unique()->numberBetween(70000000001, 79999999999)),
            'email_verified_at' => now(),
            'password' => bcrypt('123456'),
            'remember_token' => Str::random(10),
        ];
    }

    public function asOwner(): self
    {
        return $this->state(function () {
            return [
                'role_id' => User::ROLE_ID_OWNER,
            ];
        });
    }

    public function asClient(): self
    {
        return $this->state(function () {
            return [
                'role_id' => User::ROLE_ID_CLIENT,
            ];
        });
    }

    public function unverified(): self
    {
        return $this->state(function () {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    public function withPhone(string $phone): self
    {
        return $this->state(function () use ($phone) {
            return [
                'phone' => $phone,
            ];
        });
    }

    public function configure(): self
    {
        return $this->afterCreating(function (User $user) {
            $directory = storage_path('seeds/avatars');
            $files = File::files($directory);

            $user->addMedia($files[rand(0,2)])->preservingOriginal()->toMediaCollection('avatars');
        });
    }
}
