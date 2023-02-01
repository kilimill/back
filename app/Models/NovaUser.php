<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\NovaUserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property-read int id
 * @property string|null name
 * @property string|null email
 * @property string phone
 * @property Carbon|null email_verified_at
 * @property string|null password
 * @property string|null avatar
 * @property string|null remember_token
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @method static NovaUserFactory factory($count = null, $state = [])
 */
class NovaUser extends Authenticatable implements HasMedia
{
    use InteractsWithMedia, HasApiTokens, HasFactory, Notifiable;

    protected $casts = [
        'email_verified_at' => 'datetime',
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
}
