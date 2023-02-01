<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Api\NolloApiRequest;
use App\Rules\MobilePhoneRule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

/**
 * @property-read string code
 * @property-read string phone
 */
class RegisterCodeRequest extends NolloApiRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
            'phone' => ['required', new MobilePhoneRule()],
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
