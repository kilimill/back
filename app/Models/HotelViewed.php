<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int id
 * @property string user_id
 * @property string hotel_id
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 */
class HotelViewed extends Model
{
    protected $table = 'viewed_hotels';
}
