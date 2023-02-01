<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int id
 * @property int|null user_id
 * @property int status_id
 * @property int type_id
 * @property string phone
 * @property int|null code
 * @property string|null message
 * @property string|null error
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @property-read User $user
 */
class ConfirmationCode extends Model
{
    const STATUS_ID_INIT = 1;
    const STATUS_ID_SEND = 2;
    const STATUS_ID_ERROR = 3;
    const STATUS_ID_SUCCESS = 4;

    const STATUSES = [
        self::STATUS_ID_INIT => 'Подготовка к отправке',
        self::STATUS_ID_SEND => 'Отправлено',
        self::STATUS_ID_ERROR => 'Ошибка',
        self::STATUS_ID_SUCCESS => 'Доставлено',
    ];

    const TYPE_ID_SMS = 1;
    const TYPE_ID_CALL = 2;

    const TYPES = [
        self::TYPE_ID_SMS => 'Смс',
        self::TYPE_ID_CALL => 'Зконок',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
