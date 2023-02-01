<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            NovaUserSeeder::class,
            UserHotelOwnerSeeder::class,
            CitySeeder::class,
            LakeSeeder::class,
            TagSeeder::class,
            HotelSeeder::class,
        ]);
    }
}
