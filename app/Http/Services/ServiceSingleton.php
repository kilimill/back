<?php

namespace App\Http\Services;

trait ServiceSingleton
{
    /**
     * @return static
     */
    public static function make()
    {
        return resolve(self::class);
    }
}
