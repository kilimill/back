<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property-read int id
 * @property int hotel_id
 * @property int status_id
 * @property int|null user_id
 * @property string guest_name
 * @property string phone
 * @property string|null email
 * @property string|null comment
 * @property int adult_count
 * @property int|null child_count
 * @property Carbon check_in
 * @property Carbon check_out
 * @property int count_nights
 * @property int|null discount
 * @property int total_price
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @property-read Hotel $hotel
 * @property-read Collection $rooms
 * @property-read User $user
 *
 * @method static BookingFactory factory($count = null, $state = [])
 */
class Booking extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    const STATUS_ID_PREPARE = 1;
    const STATUS_ID_PROCESS = 2;
    const STATUS_ID_CONFIRMED = 3;
    const STATUS_ID_REJECTED = 4;

    const STATUS_IDS = [
        self::STATUS_ID_PREPARE => 'Заполнение',
        self::STATUS_ID_PROCESS => 'Ожидает подтверждения',
        self::STATUS_ID_CONFIRMED => 'Подтверждено',
        self::STATUS_ID_REJECTED => 'Отклонено',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class);
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
