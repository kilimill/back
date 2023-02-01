<?php

namespace App\Nova;

use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class Room extends Resource
{
    public static string $model = \App\Models\Room::class;

    public static $title = 'name';

    public static $search = [
        'id', 'name',
    ];

    public static function label(): string
    {
        return 'Номера';
    }

    public static function singularLabel(): string
    {
        return 'Номер';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Наименование', 'name')
                ->rules('required', 'max:255')
                ->sortable()
                ->hideFromIndex(),

            BelongsTo::make('Отель', 'hotel', Hotel::class)
                ->viewable(false)
                ->rules('required')
                ->sortable()
                ->searchable(),

            Textarea::make('Описание', 'description')
                ->rules( 'required', 'min:5', 'max:2000')
                ->hideFromIndex(),

            Images::make('Фото', 'media')
                ->conversionOnDetailView('thumb')
                ->conversionOnForm('thumb')
                ->rules( 'max:1')
                ->singleImageRules('dimensions:min_width=100')
                ->customPropertiesFields([
                    Boolean::make('Preview'),
                ])
                ->hideFromIndex(),

            Select::make('Питание', 'meals_id')
                ->options(\App\Models\Room::MEALS_IDS)
                ->displayUsingLabels()
                ->sortable(),

            Number::make('Количество гостей', 'guest_count')
                ->rules('required')
                ->min(1)
                ->max(100)
                ->step(1)
                ->sortable(),

            Number::make('Группа', 'group_id')
                ->hideFromIndex(),

            Currency::make('Стоимость', 'price')
                ->rules('required')
                ->min(10)
                ->max(1000000)
                ->currency('RUB')
                ->sortable(),


            Currency::make('Стоимость в выходные', 'price_weekend')
                ->rules('required')
                ->min(10)
                ->max(1000000)
                ->currency('RUB')
                ->sortable(),

            DateTime::make('Дата добавления', 'created_at')
                ->onlyOnDetail()
                ->sortable(),

            MorphToMany::make('Теги', 'tags', Tag::class),

            BelongsToMany::make('Бронирования', 'bookings', Booking::class),
        ];
    }
}
