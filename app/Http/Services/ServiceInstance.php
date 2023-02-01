<?php

namespace App\Http\Services;

trait ServiceInstance
{
    /**
     * @return static
     */
    public static function create()
    {
        return resolve(self::class);
    }
}
