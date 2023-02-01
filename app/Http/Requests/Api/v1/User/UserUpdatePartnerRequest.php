<?php

namespace App\Http\Requests\Api\v1\User;

use App\Http\Requests\Api\NolloApiRequest;
use App\Models\User;
use App\Rules\MobilePhoneRule;
use Illuminate\Validation\Rule;

/**
 * @property-read string first_name_owner
 * @property-read string last_name_owner
 * @property-read string phone_owner
 * @property-read string email_owner
 * @property-read string inn_owner
 * @property-read string|null kpp_owner
 * @property-read int legal_form_id
 */
class UserUpdatePartnerRequest extends NolloApiRequest
{
    public function rules(): array
    {
        return [
            'first_name_owner' => ['required', 'string', 'min:2', 'max:30'],
            'last_name_owner' => ['required', 'string', 'min:2', 'max:30'],
            'phone_owner' => ['required', new MobilePhoneRule()],
            'email_owner' => ['required', 'email:filter'],
            'inn_owner' => ['required', 'string', 'min:10', 'max:12'],
            'kpp_owner' => ['nullable', 'string', 'max:9'],
            'legal_form_id' => ['required', 'integer', Rule::in(array_keys(User::LEGAL_FORM_IDS))],
            'accept' => ['required', 'accepted'],
            'agree' => ['required', 'accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name_owner' => 'Имя партнера',
            'last_name_owner' => 'Фамилия партнера',
            'phone_owner' => 'Телефон партнера',
            'email_owner' => 'Email партнера',
            'inn_owner' => 'ИНН партнера',
            'kpp_owner' => 'КПП партнера',
            'legal_form_id' => 'Организационно-правовая форма партнера',
            'accept' => 'Принятие условий договора оферты',
            'agree' => 'Согласие с политикой конфиденциальности',
        ];
    }
}
