<?php

namespace Database\Seeders;

use App\Models\Lake;
use Illuminate\Database\Seeder;

class LakeSeeder extends Seeder
{
    public function run(): void
    {
        $lakes = ['Сенгилеевское озеро', 'Новотроицкое озеро', 'Черное море', 'Азовское море', 'Река Волга'];
        foreach ($lakes as $lake) {
            Lake::factory()->create(['name' => $lake]);
        }
    }
}
