<?php

namespace App\Nova;

use App\Rules\MobilePhoneRule;
use Dniccum\PhoneNumber\PhoneNumber;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class User extends Resource
{
    public static string $model = \App\Models\User::class;

    public static $title = 'name';

    public static $search = [
        'id', 'name', 'email',
    ];

    public static function label(): string
    {
        return 'Пользователи';
    }

    public static function singularLabel(): string
    {
        return 'Пользователь';
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

            Select::make('Роль', 'role_id')
                ->rules('required')
                ->options(\App\Models\User::ROLE_IDS)
                ->displayUsingLabels()
                ->sortable(),

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

            HasMany::make('Отели', 'hotels', Hotel::class),

            HasMany::make('Бронирования', 'bookings', Booking::class),
        ];
    }
}
