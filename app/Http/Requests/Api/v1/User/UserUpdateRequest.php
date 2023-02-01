<?php

namespace App\Http\Requests\Api\v1\User;

use App\Http\Requests\Api\NolloApiRequest;
use App\Models\User;
use App\Rules\MobilePhoneRule;
use Illuminate\Validation\Rule;

/**
 * @property-read string name
 * @property-read string|null avatar
 * @property-read string phone
 * @property-read string email
 */
class UserUpdateRequest extends NolloApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:30'],
            'avatar' => ['filled', 'image', 'mimes:jpeg,png,jpg,gif', 'max:10240'],
            'phone' => [
                'required',
                new MobilePhoneRule(),
                Rule::unique((new User())->getTable(), 'phone')->ignore(auth_user_or_fail()->getKey()),
            ],
            'email' => [
                'required',
                'email:filter',
                Rule::unique((new User())->getTable(), 'email')->ignore(auth_user_or_fail()->getKey()),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Имя',
            'avatar' => 'Аватар',
            'phone' => 'Телефон',
            'email' => 'Электронная почта',
        ];
    }
}
