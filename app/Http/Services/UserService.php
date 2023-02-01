<?php

namespace App\Http\Services;

use App\Exceptions\SmscSendException;
use Illuminate\Support\Facades\Cache;

class UserService
{
    use ServiceInstance;

    /**
     * @throws SmscSendException
     */
    public function sendConfirmationCode(string $phone): void
    {
        $code = SmscService::create()->sendCode($phone);
        Cache::put($phone, $code, now()->addMinutes(2));
    }
}
