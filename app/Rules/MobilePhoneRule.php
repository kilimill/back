<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MobilePhoneRule implements Rule
{
    /**
     * @param string $attribute
     * @param mixed $value
     */
    public function passes($attribute, $value): bool
    {
        if (!$value) {
            return false;
        }

        $phone = preg_replace('/\D/', '', $value);

        if (!$phone === '') {
            return false;
        }

        if (!str_starts_with($phone, '7')) {
            return false;
        }

        if (strlen($value) !== 11) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return 'Поле :attribute имеет неправильный формат.';
    }
}
