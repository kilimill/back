<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property string name
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @property-read Collection $regions
 * @property-read Collection $hotels
 *
 * @method static self first()
 * @method static CountryFactory factory($count = null, $state = [])
 */
class Country extends Model
{
    use HasFactory;

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }

    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class);
    }

    public static function findRussia(): ?self
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return self::query()->where('name', 'Россия')->first();
    }
}
