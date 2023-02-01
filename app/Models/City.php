<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\CityFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int id
 * @property string name
 * @property int region_id
 * @property int country_id

 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @property-read Region $region
 * @property-read Country $country
 * @property-read Collection $hotels
 *
 * @method static CityFactory factory($count = null, $state = [])
 */
class City extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class);
    }
}
