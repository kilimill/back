<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        Tag::factory(10)->sequence(
            [
                'id' => 1,
                'name' => 'Активный отдых',
                'icon' => file_get_contents('storage/seeds/tags/ActiveIcon.svg'),
            ],
            [
                'id' => 2,
                'name' => 'Рыбалка',
                'icon' => file_get_contents('storage/seeds/tags/FishingIcon.svg'),
            ],
            [
                'id' => 3,
                'name' => 'Охота',
                'icon' => file_get_contents('storage/seeds/tags/HuntIcon.svg'),
            ],
            [
                'id' => 4,
                'name' => 'На берегу',
                'icon' => file_get_contents('storage/seeds/tags/BeachIcon.svg'),
            ],
            [
                'id' => 5,
                'name' => 'Баня и сауна',
                'icon' => file_get_contents('storage/seeds/tags/Barrelcon.svg'),
            ],
            [
                'id' => 6,
                'name' => 'Праздники',
                'icon' => file_get_contents('storage/seeds/tags/PartyIcon.svg'),
            ],
            [
                'id' => 7,
                'name' => 'Корпоративный отдых',
                'icon' => file_get_contents('storage/seeds/tags/CorpIcon.svg'),
            ],
            [
                'id' => 8,
                'name' => 'Подводная охота',
                'icon' => file_get_contents('storage/seeds/tags/UnderwaterIcon.svg'),
            ],
            [
                'id' => 9,
                'name' => 'Кемпинг',
                'icon' => file_get_contents('storage/seeds/tags/CampIcon.svg'),
            ],
            [
                'id' => 10,
                'name' => 'Сплав/Поход',
                'icon' => file_get_contents('storage/seeds/tags/HikeIcon.svg'),
            ],
            [
                'id' => 11,
                'name' => 'Романтический отдых',
                'icon' => file_get_contents('storage/seeds/tags/HeartFilterIcon.svg'),
            ],
        )->create();
    }
}
