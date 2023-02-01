<?php

namespace App\Nova;

use App\Models\Hotel as HotelModel;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Jexme\YandexMapPicker\YandexMapPicker;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Place;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Status;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Hotel extends Resource
{
    public static string $model = HotelModel::class;

    public static $title = 'name';

    public static $search = [
        'id',
        'name',
        'city.name',
    ];

    public static function label(): string
    {
        return 'Отели';
    }

    public static function singularLabel(): string
    {
        return 'Отель';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Наименование', 'name')
                ->rules('nullable', 'min:2', 'max:255')
                ->sortable(),

            Status::make('Статус', 'status_id', function ($status) {
                return match ($status) {
                    HotelModel::STATUS_ID_DRAFT => HotelModel::STATUS_IDS[HotelModel::STATUS_ID_DRAFT],
                    HotelModel::STATUS_ID_UNDER_REVIEW => HotelModel::STATUS_IDS[HotelModel::STATUS_ID_UNDER_REVIEW],
                    HotelModel::STATUS_ID_ACTIVE => HotelModel::STATUS_IDS[HotelModel::STATUS_ID_ACTIVE],
                    HotelModel::STATUS_ID_REJECTED => HotelModel::STATUS_IDS[HotelModel::STATUS_ID_REJECTED],
                };
            })
                ->loadingWhen([HotelModel::STATUS_IDS[HotelModel::STATUS_ID_DRAFT], HotelModel::STATUS_IDS[HotelModel::STATUS_ID_UNDER_REVIEW]])
                ->failedWhen([HotelModel::STATUS_IDS[HotelModel::STATUS_ID_REJECTED]])
                ->onlyOnIndex()
                ->sortable(),

            Select::make('Статус', 'status_id')
                ->rules('required')
                ->options(HotelModel::STATUS_IDS)
                ->displayUsingLabels()
                ->readonly()
                ->hideFromIndex(),

            Textarea::make('Описание', 'description')
                ->rules('nullable', 'min:5', 'max:2000')
                ->hideFromIndex(),

            Select::make('Тип', 'type_id')
                ->options(HotelModel::TYPE_IDS)
                ->displayUsingLabels()
                ->hideFromIndex(),

            BelongsTo::make('Страна', 'country', Country::class)
                ->viewable(false)
                ->hideFromIndex(),

            BelongsTo::make('Регион', 'region', Region::class)
                ->viewable(false)
                ->exceptOnForms()
                ->sortable(),

            BelongsTo::make('Населенный пункт', 'city', City::class)
                ->viewable(false)
                ->exceptOnForms()
                ->sortable(),

            Select::make('Регион', 'region_id')
                ->dependsOn(
                    'country',
                    function (Select $field, NovaRequest $request, FormData $formData) {
                        $country = \App\Models\Country::whereId($formData->country ?? $this->country_id)->first();
                        if ($country != null) {
                            $field->options($country->regions()->pluck('name', 'id'));
                        }
                    }
                )->onlyOnForms()
                ->searchable(),

            Select::make('Населенный пункт', 'city_id')
                ->dependsOn(
                    'region_id',
                    function (Select $field, NovaRequest $request, FormData $formData) {
                        $region = \App\Models\Region::whereId($formData->region_id ?? $this->region_id)->first();
                        if ($region != null) {
                            $field->options($region->cities()->pluck('name', 'id'));
                        }
                    }
                )->onlyOnForms()
                ->searchable(),

            // TODO Place API is deprecated
            Place::make('Адрес', 'address')
                ->hideFromIndex()
                ->sortable(),

            YandexMapPicker::make('Координаты', 'coordinates')
                ->hideFromIndex(),

            Images::make('Фото', 'media')
                ->conversionOnDetailView('thumb')
                ->conversionOnForm('thumb')
                ->rules('max:15')
                ->singleImageRules('dimensions:min_width=100')
                ->customPropertiesFields([
                    Boolean::make('Preview'),
                ])
                ->hideFromIndex(),

//            Number::make('Удаленность от населенного пункта (км)', 'distance_city')
//                ->step(1)
//                ->hideFromIndex(),

            Textarea::make('Как добраться', 'detailed_route')
                ->rules('nullable', 'min:5', 'max:2000')
                ->hideFromIndex(),

            Textarea::make('Особые условия', 'conditions')
                ->rules('nullable', 'min:5', 'max:2000')
                ->hideFromIndex(),

            Select::make('Сезон работы', 'season_id')
                ->options(\App\Models\Hotel::SEASON_IDS)
                ->displayUsingLabels()
                ->hideFromIndex(),

            BelongsTo::make('Кто добавил отель', 'user', User::class)
                ->rules('required')
                ->sortable(),

            Number::make('Минимальный срок бронирования (дни)', 'min_days')
                ->rules('nullable', 'min:1', 'max:60')
                ->min(1)
                ->step(1)
                ->hideFromIndex(),

            Number::make('Час заезда', 'check_in_hour')
                ->rules('nullable', 'min:0', 'max:23')
                ->min(0)
                ->max(23)
                ->step(1)
                ->hideFromIndex(),

            Number::make('Час выезда', 'check_out_hour')
                ->rules('nullable', 'min:0', 'max:23')
                ->min(0)
                ->max(23)
                ->step(1)
                ->hideFromIndex(),

            MorphToMany::make('Теги', 'tags', Tag::class),

            DateTime::make('Дата создания', 'created_at')
                ->onlyOnDetail()
                ->showOnIndex()
                ->sortable(),

            DateTime::make('Дата обновления', 'updated_at')
                ->onlyOnDetail(),

            HasMany::make('Контакты', 'contacts', Contact::class),

            HasMany::make('Номера', 'rooms', Room::class),

            HasMany::make('Бронирования', 'bookings', Booking::class),

            BelongsToMany::make('Водоёмы', 'lakes', Lake::class)
                ->fields(function () {
                    return [
                        Number::make('Удаленность от берега', 'distance_shore'),
                    ];
                }),
        ];
    }
}
