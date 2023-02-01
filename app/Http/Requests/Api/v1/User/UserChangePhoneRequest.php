<?php

namespace App\Http\Requests\Api\v1\User;

use App\Http\Requests\Api\NolloApiRequest;
use App\Models\User;
use App\Rules\MobilePhoneRule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * @property User $user
 * @property string $code
 * @property string $phone
 */
class UserChangePhoneRequest extends NolloApiRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
            'phone' => [
                'required',
                new MobilePhoneRule(),
                Rule::unique((new User())->getTable(), 'phone')->ignore(auth_user_or_fail()->getKey()),
            ],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function rulesPassed(): void
    {
        $code = Cache::get($this->phone);

        if (!$code || $code !== $this->code) {
            throw ValidationException::withMessages([
                'code' => 'Код не подходит или срок его действия истек.',
            ]);
        }
    }

    public function attributes(): array
    {
        return [
            'code' => 'Код',
            'phone' => 'Телефон',
        ];
    }
}
