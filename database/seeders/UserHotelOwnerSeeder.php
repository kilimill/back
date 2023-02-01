<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserHotelOwnerSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->asOwner()->create([
            'email' => 'onwer@onwer.com',
            'phone' => '78888888802',
        ]);
    }
}
