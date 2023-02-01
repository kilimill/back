<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\RegionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property string name
 * @property int country_id
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @property-read Country $country
 * @property-read Collection $cities
 * @property-read Collection $hotels
 *
 * @method static RegionFactory factory($count = null, $state = [])
 */
class Region extends Model
{
    use HasFactory;

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class);
    }
}
