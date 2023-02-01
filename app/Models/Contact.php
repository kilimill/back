<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property-read int id
 * @property int type_id
 * @property string value
 * @property int hotel_id
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @property-read Hotel $hotel
 *
 * @method static ContactFactory factory($count = null, $state = [])
 */
class Contact extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    const TYPE_ID_PHONE = 1;
    const TYPE_ID_EMAIL = 2;
    const TYPE_ID_SITE = 3;
    const TYPE_ID_TELEGRAM = 4;
    const TYPE_ID_VK = 5;

    const TYPE_IDS = [
        self::TYPE_ID_PHONE => 'Телефон',
        self::TYPE_ID_EMAIL => 'Email',
        self::TYPE_ID_SITE => 'Сайт',
        self::TYPE_ID_TELEGRAM => 'Telegram',
        self::TYPE_ID_VK => 'VK',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
