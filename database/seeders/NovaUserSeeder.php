<?php

namespace Database\Seeders;

use App\Models\NovaUser;
use Illuminate\Database\Seeder;

class NovaUserSeeder extends Seeder
{
    public function run(): void
    {
        NovaUser::factory()->create([
            'email' => 'admin@admin.com',
            'phone' => '78888888801',
        ]);
    }
}
