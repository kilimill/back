<?php

namespace App\Nova;

use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Lake extends Resource
{
    public static string $model = \App\Models\Lake::class;

    public static string $name = 'name';

    public static $search = [
        'id', 'name',
    ];

    public static function label(): string
    {
        return 'Водоёмы';
    }

    public static function singularLabel(): string
    {
        return 'Водоём';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Наименование', 'name')
                ->rules('required', 'max:255')
                ->sortable()
                ->hideFromIndex(),

            BelongsToMany::make('Отели', 'hotels', Hotel::class)
                ->fields(function () {
                    return [
                        Number::make('Удаленность от берега (м)', 'distance_shore'),
                    ];
                }),
        ];
    }
}
