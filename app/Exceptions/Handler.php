<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): JsonResponse|HttpFoundationResponse
    {
        // dd(Util::isNovaRequest($request));

        if(config('app.debug')) {
            return parent::render($request, $e);
        }

        if (Str::is($request->segment(1), 'api')) {
            return match (true) {
                $e instanceof ModelNotFoundException => $this->modelNotFound(),
                $e instanceof NotFoundHttpException => $this->routeNotFound(),
                $e instanceof ValidationException => $this->validationError($e),
                $e instanceof ApiHotelValidationException => $this->hotelValidationError($e),
                $e instanceof ApiException => $this->badApiRequest($e),
                $e instanceof TokenMismatchException, $e instanceof AuthenticationException => $this->notAuthenticated(),
                default => parent::render($request, $e),
            };
        }

        return parent::render($request, $e);
    }

    private function badApiRequest(Throwable $e): JsonResponse
    {
        // TODO log this
        return response()->json(['message' => $e->getMessage()], $e->getCode());
    }

    private function modelNotFound(): JsonResponse
    {
        return response()->json(['message' => 'Запись не найдена.'], HttpFoundationResponse::HTTP_NOT_FOUND);
    }

    private function routeNotFound(): JsonResponse
    {
        return response()->json(['message' => 'URL не существует.'], HttpFoundationResponse::HTTP_NOT_FOUND);
    }

    private function notAuthenticated(): JsonResponse
    {
        return response()->json(['message' => 'Запрос не авторизован.'], HttpFoundationResponse::HTTP_UNAUTHORIZED);
    }

    private function hotelValidationError(ApiHotelValidationException $e): JsonResponse
    {
        return response()->json([
            'message' => 'В данных отеля есть ошибки.',
            'errors' => $e->getErrors(),
        ], $e->getCode());
    }

    private function validationError(ValidationException $e): JsonResponse
    {
        return response()->json([
            'errors' => $e->errors(),
        ], $e->status);
    }
}
