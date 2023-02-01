<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Api\NolloApiRequest;
use App\Models\User;
use App\Rules\MobilePhoneRule;
use Illuminate\Validation\Rule;

/**
 * @property-read string phone
 */
class RegisterRequest extends NolloApiRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['required', new MobilePhoneRule()],
        ];
    }

    public function attributes(): array
    {
        return [
            'phone' => 'Телефон',
        ];
    }
}
