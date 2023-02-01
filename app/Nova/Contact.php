<?php

namespace App\Nova;

use App\Models\Contact as ContactModel;
use App\Rules\MobilePhoneRule;
use Egulias\EmailValidator\EmailValidator;
use Illuminate\Validation\Concerns\FilterEmailValidation;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Contact extends Resource
{
    public static string $model = ContactModel::class;

    public static $title = 'name';

    public static $search = [
        'id', 'value',
    ];

    public static function label(): string
    {
        return 'Контакты';
    }

    public static function singularLabel(): string
    {
        return 'Контакт';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Select::make('Тип', 'type_id')
                ->options(ContactModel::TYPE_IDS)
                ->displayUsingLabels()
                ->rules('required')
                ->sortable(),

            Text::make('Контакт', 'value')
                ->rules('required', function($attribute, $value, $fail) use ($request) {
                    $typeId = intval($request->get('type_id'));

                    if ($typeId === ContactModel::TYPE_ID_PHONE) {
                        $rule = (new MobilePhoneRule());
                        $passed = $rule->passes($attribute, $value);

                        if (!$passed) {
                            return $fail('Телефон имеет неправильный формат. (Пример 78889991122)');
                        }
                    }

                    if ($typeId === ContactModel::TYPE_ID_EMAIL) {
                        $validator = new EmailValidator();
                        $passed = $validator->isValid($value, new FilterEmailValidation()); //true

                        if (!$passed) {
                            return $fail('Email имеет неправильный формат.');
                        }
                    }
                })
                ->sortable(),

            BelongsTo::make('Отель', 'hotel', Hotel::class)
                ->sortable()
                ->searchable()
                ->viewable(false),
        ];
    }
}
