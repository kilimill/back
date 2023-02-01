<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property-read int id
 * @property string name
 * @property string|null icon
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @property-read Collection $hotels
 * @property-read Collection $rooms
 *
 * @method static TagFactory factory($count = null, $state = [])
 */
class Tag extends Model
{
    use HasFactory;

    public function hotels(): MorphToMany
    {
        return $this->morphedByMany(Hotel::class, 'taggable');
    }

    public function rooms(): MorphToMany
    {
        return $this->morphedByMany(Room::class, 'taggable');
    }
}
