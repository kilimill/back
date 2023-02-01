<?php

namespace App\Exceptions;

class SmscSendException extends ApiException
{
    protected $message = 'Ошибка при отправке кода.';
}
