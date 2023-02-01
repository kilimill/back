<?php

namespace App\Exceptions;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ApiHotelValidationException extends ApiException
{
    protected $code = ResponseAlias::HTTP_UNPROCESSABLE_ENTITY;
    protected $message = 'Некоторые поля заполнены неверно.';

    protected array $errors = [];

    const STEP_1 = 'index';
    const STEP_2 = 'categories';
    const STEP_3 = 'photos';
    const STEP_4 = 'contacts';
    const STEP_5 = 'address';
    const STEP_6 = 'rooms';
    const STEP_7 = 'lakes';
    const STEP_8 = 'info';
    const EXTRA = 'extra';

    const STEPS = [
        self::STEP_1 => [
            'name',
            'type_id',
            'description',
        ],
        self::STEP_2 => [
            'tags',
            'tags.*',
        ],
        self::STEP_3 => [
            'media',
            'media.*',
        ],
        self::STEP_4 => [
            'contacts',
            'contacts.*.id',
            'contacts.*.type_id',
            'contacts.*.value',
        ],
        self::STEP_5 => [
            'address',
            'coordinates',
        ],
        self::STEP_6 => [
            'rooms',
            'rooms.*.id',
            'rooms.*.name',
            'rooms.*.description',
            'rooms.*.guest_count',
            'rooms.*.meals_id',
            'rooms.*.quantity',
            'rooms.*.price',
            'rooms.*.price_weekend',
            'rooms.*.media',
            'rooms.*.media.*',
        ],
        self::STEP_7 => [
            'lakes',
            'lakes.*.id',
            'lakes.*.distance_shore',
        ],
        self::STEP_8 => [
            'conditions',
            'detailed_route',
            'season_id',
            'min_days',
            'check_in_hour',
            'check_out_hour',
        ],
        self::EXTRA => [
            'status_id',
        ],
    ];


    public static function fromLaravel(LaravelValidationException $e): self
    {
        $self = (new self($e->getMessage()));
        $self->setErrors($e->errors());

        return $self;
    }

    public function addError(string $inputKey, string $message): self
    {
        $this->errors[$inputKey][] = $message;

        return $this;
    }

    protected function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        $newErrors = [];

        collect($this->errors)->map(function (array $error, string $errorKey) use (&$newErrors) {
            $key = collect(self::STEPS)->search(function (array $step) use ($errorKey) {
                return in_array(preg_replace('/[0-9]+/', '*', $errorKey), $step);
            });

            if ($key) {
                $newErrors[$key][$errorKey] = $error;
            }
        });

        return $newErrors;
    }

    public function getValidKeys(): array
    {
        return collect(self::STEPS)
            ->except(collect($this->getErrors())->keys())
            ->keys()
            ->toArray();
    }
}
