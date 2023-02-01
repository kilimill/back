<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property-read int id
 * @property string name
 * @property int hotel_id
 * @property int|null group_id
 * @property string description
 * @property int guest_count
 * @property int meals_id
 * @property int price
 * @property int price_weekend
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property Carbon|null deleted_at
 *
 * @property-read Hotel $hotel
 * @property-read Collection $media
 * @property-read Collection $bookings
 * @property-read Collection $tags
 *
 * @method static RoomFactory factory($count = null, $state = [])
 */
class Room extends Model implements HasMedia, Auditable
{
    use HasFactory, InteractsWithMedia, \OwenIt\Auditing\Auditable, SoftDeletes;

    const MEALS_ID_1 = 1;
    const MEALS_ID_2 = 2;
    const MEALS_ID_3 = 3;
    const MEALS_ID_4 = 4;
    const MEALS_ID_5 = 5;
    const MEALS_ID_6 = 6;

    const MEALS_IDS = [
        self::MEALS_ID_1 => 'Без питания',
        self::MEALS_ID_2 => 'Завтрак',
        self::MEALS_ID_3 => 'Трехразовое питание',
        self::MEALS_ID_4 => 'Двухразовое питание',
        self::MEALS_ID_5 => 'All inclusive - все включено',
        self::MEALS_ID_6 => 'Обед',
    ];

    /**
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(130)
            ->height(130);
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
