<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class Tag extends Resource
{
    public static string $model = \App\Models\Tag::class;

    public static $title = 'name';

    public static $search = [
         'name',
    ];

    public static function label(): string
    {
        return 'Теги';
    }

    public static function singularLabel(): string
    {
        return 'Тег';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->onlyOnDetail(),

            Text::make('Наименование', 'name')
                ->rules('required', 'max:255')
                ->sortable()
                ->hideFromIndex(),
        ];
    }
}
