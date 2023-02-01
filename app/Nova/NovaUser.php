<?php

namespace App\Nova;

use App\Rules\MobilePhoneRule;
use Dniccum\PhoneNumber\PhoneNumber;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class NovaUser extends Resource
{
    public static string $model = \App\Models\NovaUser::class;

    public static $title = 'name';

    public static $search = [
        'id', 'name', 'email',
    ];

    public static function label(): string
    {
        return 'Администраторы';
    }

    public static function singularLabel(): string
    {
        return 'Администраторы';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Имя', 'name')
                ->rules( 'nullable', 'min:2', 'max:30')
                ->sortable(),

            Images::make('Аватар', 'avatars')
                ->conversionOnIndexView('thumb')
                ->rules('max:1')
                ->hideFromIndex(),

            Text::make('Email')
                ->rules('nullable', 'email')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            PhoneNumber::make('Телефон', 'phone')
                ->disableValidation()
                ->format('###########')
                ->rules(['required', new MobilePhoneRule()])
                ->creationRules('unique:users,phone')
                ->updateRules('unique:users,phone,{{resourceId}}'),
        ];
    }
}
