<?php

use App\Exceptions\ApiLogicException;
use App\Models\User;

if (! function_exists('auth_user_or_fail')) {
    /** @noinspection PhpUnhandledExceptionInspection */
    function auth_user_or_fail(): User
    {
        /** @var User $user */
        throw_if(!$user = auth()->user(), ApiLogicException::class);

        return $user;
    }
}

if (! function_exists('auth_user_or_null')) {
    /** @noinspection PhpUnhandledExceptionInspection */
    function auth_user_or_null(): User|null
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }
}
