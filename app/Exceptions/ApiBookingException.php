<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ApiBookingException extends ApiException
{
    protected $code = ResponseAlias::HTTP_NOT_FOUND;
    protected $message = 'Бронирование не найдено.';
}
