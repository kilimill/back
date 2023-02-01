<?php

namespace App\Rules;

use App\Models\Contact;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ContactRule implements Rule
{
    private mixed $contacts;

    public function __construct(mixed $contacts)
    {
        $this->contacts = $contacts;
    }

    /**
     * @param string $attribute
     * @param mixed $value
     */
    public function passes($attribute, $value): bool
    {
        $index = explode('.', $attribute)[1];
        $typeId = intval($this->contacts[$index]['type_id'] ?? 0);

        return match ($typeId) {
            Contact::TYPE_ID_PHONE => $this->validate($value, [new MobilePhoneRule()]),
            Contact::TYPE_ID_EMAIL => $this->validate($value, ['email:filter']),
            Contact::TYPE_ID_SITE, Contact::TYPE_ID_VK, Contact::TYPE_ID_TELEGRAM => $this->validate($value, ['string']),
            default => false,
        };
    }

    private function validate(string $value, $rules): bool
    {
        try {
            Validator::make([$value], $rules)->validate();
        } catch (ValidationException $e) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return 'Поле :attribute имеет неправильный формат.';
    }
}
