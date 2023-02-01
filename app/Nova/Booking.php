<?php

namespace App\Nova;

use App\Http\Services\BookingService;
use App\Models\Booking as BookinglModel;
use App\Rules\MobilePhoneRule;
use Dniccum\PhoneNumber\PhoneNumber;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Status;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Booking extends Resource
{
    public static string $model = \App\Models\Booking::class;

    public static $title = 'name';

    public static $search = [
        'id', 'hotel_id',
    ];

    public static function label(): string
    {
        return 'Бронирования';
    }

    public static function singularLabel(): string
    {
        return 'Бронирование';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Отель', 'hotel', Hotel::class)
                ->rules('required')
                ->sortable()
                ->searchable(),

            BelongsTo::make('Пользователь', 'user', User::class)
                ->viewable(false)
                ->nullable()
                ->sortable(),

            Status::make('Статус', 'status_id', function ($status) {
                return match ($status) {
                    BookinglModel::STATUS_ID_PREPARE => BookinglModel::STATUS_IDS[BookinglModel::STATUS_ID_PREPARE],
                    BookinglModel::STATUS_ID_PROCESS => BookinglModel::STATUS_IDS[BookinglModel::STATUS_ID_PROCESS],
                    BookinglModel::STATUS_ID_CONFIRMED => BookinglModel::STATUS_IDS[BookinglModel::STATUS_ID_CONFIRMED],
                    BookinglModel::STATUS_ID_REJECTED => BookinglModel::STATUS_IDS[BookinglModel::STATUS_ID_REJECTED],
                };
            })
                ->loadingWhen([BookinglModel::STATUS_IDS[BookinglModel::STATUS_ID_PREPARE], BookinglModel::STATUS_IDS[BookinglModel::STATUS_ID_PROCESS]])
                ->failedWhen([BookinglModel::STATUS_IDS[BookinglModel::STATUS_ID_REJECTED]])
                ->onlyOnIndex()
                ->sortable(),

            Select::make('Статус', 'status_id')
                ->rules('required')
                ->options(\App\Models\Booking::STATUS_IDS)
                ->displayUsingLabels()
                ->hideFromIndex(),

            Text::make('Имя гостя', 'guest_name')
                ->rules('required', 'min:2', 'max:30')
                ->sortable(),

            PhoneNumber::make('Телефон', 'phone')
                ->disableValidation()
                ->format('###########')
                ->rules(['required', new MobilePhoneRule()])
                ->hideFromIndex(),

            Text::make('Email')
                ->rules('required', 'email', 'max:254')
                ->hideFromIndex(),

            Textarea::make('Комментарий', 'comment')
                ->rules('required', 'min:5', 'max:500')
                ->hideFromIndex(),

            Number::make('Взрослые', 'adult_count')
                ->rules('required')
                ->min(1)
                ->max(30)
                ->step(1)
                ->sortable(),

            Number::make('Дети', 'child_count')
                ->min(0)
                ->max(30)
                ->step(1)
                ->sortable(),

            Date::make('Заезд', 'check_in')
                ->rules('required', 'after_or_equal:today')
                ->sortable(),

            Date::make('Выезд', 'check_out')
                ->rules('required','after:check_in', 'after:today')
                ->sortable(),

            Number::make('Количество ночей', 'count_nights')
                ->readonly(),

            Number::make('Скидка', 'discount')
                ->hideFromIndex(),

            Currency::make('Итоговая cтоимость', 'total_price')
                ->rules('required')
                ->currency('RUB')
                ->readonly()
                ->sortable()
                ->hideFromIndex(),

            DateTime::make('Дата создания', 'created_at')
                ->onlyOnDetail()
                ->showOnIndex()
                ->sortable(),

            DateTime::make('Дата обновления', 'updated_at')
                ->onlyOnDetail(),

            BelongsToMany::make('Номера', 'rooms', Room::class),
        ];
    }
}
