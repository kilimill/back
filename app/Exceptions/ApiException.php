<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ApiException extends Exception
{
    protected $code = ResponseAlias::HTTP_INTERNAL_SERVER_ERROR;
    protected $message = 'Ошибка API.';
}
