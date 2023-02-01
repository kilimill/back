<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AddressRule implements Rule
{
    /**
     * @param string $attribute
     * @param mixed $value
     */
    public function passes($attribute, $value): bool
    {
        $country = $value['country'] ?? null;
        $region = $value['province'] ?? null;
        $city = $value['locality'] ?? null;

        if (is_array($value) && $country !== 'Россия') {
            return false;
        }

        if (is_array($value) && !$region) {
            return false;
        }

        if (is_array($value) && !$city) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return 'Поле :attribute имеет неправильный формат.';
    }
}
