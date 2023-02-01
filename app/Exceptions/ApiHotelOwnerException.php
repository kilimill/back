<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ApiHotelOwnerException extends ApiException
{
    protected $code = ResponseAlias::HTTP_NOT_FOUND;
    protected $message = 'Отель не найден.';
}
