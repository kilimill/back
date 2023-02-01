<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $data = [
            'hotel_id' => Hotel::factory(),
            'status_id' => $this->faker->numberBetween(2, 4),
            'user_id' => User::factory()->asClient(),
            'guest_name' => $this->faker->name,
            'phone' => strval($this->faker->unique()->numberBetween(70000000001, 79999999999)),
            'email' => $this->faker->email,
            'comment' => $this->faker->text,
            'adult_count' =>  $this->faker->numberBetween(1, 10),
            'child_count' => $this->faker->numberBetween(1, 10),
            'check_in' => Carbon::parse($this->faker->dateTimeBetween('now', '+30 days')),
            'discount' => 0,
            'total_price' => $this->faker->randomNumber(6),
        ];
        $data['check_out'] =  Carbon::parse($this->faker->dateTimeBetween($data['check_in'], '+30 days'));
        $data['count_nights'] = $data['check_out']->diffInDays($data['check_in']);

        return $data;
    }

    public function withCheckIn(Carbon $checkIn): self
    {
        return $this->state(function () use ($checkIn) {
            return [
                'check_in' => $checkIn,
            ];
        });
    }

    public function withCheckOut(Carbon $checkOut): self
    {
        return $this->state(function () use ($checkOut) {
            return [
                'check_out' => $checkOut,
            ];
        });
    }
}
