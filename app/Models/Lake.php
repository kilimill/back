<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\LakeFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property-read int id
 * @property string name
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @property-read Collection $hotels
 *
 * @method static LakeFactory factory($count = null, $state = [])
 */
class Lake extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class)->withPivot('distance_shore');
    }
}
