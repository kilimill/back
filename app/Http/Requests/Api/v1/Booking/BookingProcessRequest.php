<?php

namespace App\Http\Requests\Api\v1\Booking;

use App\Http\Requests\Api\NolloApiRequest;
use App\Models\Booking;
use App\Models\User;
use App\Rules\MobilePhoneRule;
use Illuminate\Validation\ValidationException;

/**
 * /**
 * @property-read Booking booking
 * @property-read boolean is_another_guest
 * @property-read string guest_name
 * @property-read string phone
 * @property-read string email
 * @property-read string|null comment
 */
class BookingProcessRequest extends NolloApiRequest
{
    public function rules(): array
    {
        return [
            'is_another_guest' => ['required', 'boolean'],
            'guest_name' => ['required', 'string', 'min:2', 'max:30'],
            'phone' => ['required', new MobilePhoneRule()],
            'email' => ['required', 'email:filter'],
            'comment' => ['filled', 'string', 'min:5', 'max:500'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function rulesPassed(): void
    {
        if (!$this->is_another_guest) {
            $user = auth_user_or_fail();
            $this->validatePhoneIsUnique($user);
            $this->validateEmailIsUnique($user);
        }
    }

    /**
     * @throws ValidationException
     */
    private function validatePhoneIsUnique(User $user): void
    {
        $isNotPhoneUnique = User::query()->where('phone', $this->phone)->whereNot('id', $user->getKey())->exists();
        if ($isNotPhoneUnique) {
            throw ValidationException::withMessages([
                'phone' => 'Такое значение поля Телефон уже существует.',
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function validateEmailIsUnique(User $user): void
    {
        $isNotPhoneUnique = User::query()->where('email', $this->email)->whereNot('id', $user->getKey())->exists();
        if ($isNotPhoneUnique) {
            throw ValidationException::withMessages([
                'email' => 'Такое значение поля Электронная почта уже существует.',
            ]);
        }
    }

    public function attributes(): array
    {
        return [
            'is_another_guest' => 'Гостем будет другой человек',
            'guest_name' => 'Имя гостя',
            'phone' => 'Телефон',
            'email' => 'Электронная почта',
            'comment' => 'Комментарий',
        ];
    }
}
