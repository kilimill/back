<?php

namespace App\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class Region extends Resource
{
    public static string $model = \App\Models\Region::class;

    public static $title = 'name';

    public static $search = [
        'id', 'name',
    ];

    public static function label(): string
    {
        return 'Регионы';
    }

    public static function singularLabel(): string
    {
        return 'Регион';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->onlyOnDetail(),

            Text::make('Наименование', 'name', function () {
                $url = Nova::path()."/resources/{$this->uriKey()}/{$this->id}";
                return "<a class=\"no-underline dim text-primary font-bold\" href=\"{$url}\">{$this->name}</a>";
            })->asHtml()
                ->sortable()
                ->onlyOnIndex(),

            Text::make('Наименование', 'name')
                ->rules('required', 'max:255')
                ->hideFromIndex(),

            BelongsTo::make('Страна', 'country', Country::class)
                ->viewable(false)
                ->rules('required')
                ->sortable(),

            HasMany::make('Города', 'cities', City::class),
        ];
    }
}
