<?php

namespace App\Nova;

use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class Country extends Resource
{
    public static string $model = \App\Models\Country::class;

    public static $title = 'name';

    public static $search = [
        'id', 'name',
    ];

    public static function label(): string
    {
        return 'Страны';
    }

    public static function singularLabel(): string
    {
        return 'Страна';
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


            HasMany::make('Регионы', 'regions', Region::class),

            HasMany::make('Отели', 'hotels', Hotel::class),
        ];
    }
}
