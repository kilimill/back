<?php

namespace App\Exceptions;

class ApiLogicException extends ApiException
{
    protected $message = 'Ошибка сервера.';
}
