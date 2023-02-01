<?php

namespace App\Http\Requests\Api\v1\Hotel;

use App\Http\Requests\Api\NolloApiRequest;
use App\Http\Resources\HotelOwnerShowResource;
use App\Models\Contact;
use App\Models\Hotel;
use App\Models\Lake;
use App\Models\Room;
use App\Models\Tag;
use App\Rules\AddressRule;
use App\Rules\ContactRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * @property-read Hotel|null hotel
 * @property-read int|null type_id
 * @property-read string|null name
 * @property-read string|null description
 * @property-read array|null address
 * @property-read string|null coordinates
 * @property-read string|null detailed_route
 * @property-read string|null conditions
 * @property-read int|null season_id
 * @property-read int|null min_days
 * @property-read int|null check_in_hour
 * @property-read int|null check_out_hour
 * @property-read array tags
 * @property-read array media
 * @property-read array contacts
 * @property-read array lakes
 * @property-read array custom_lake
 * @property-read array rooms
 * @property-read int status_id
 */
class HotelUpsertRequest extends NolloApiRequest
{
    protected bool $instantValidate = false;

    public function rules(): array
    {
        return [
            // step 1
            'name' => ['filled', 'string', 'min:2', 'max:255'],
            'type_id' => ['filled', Rule::in(array_keys(Hotel::TYPE_IDS))],
            'description' => ['filled', 'string', 'min:5', 'max:2000'],
            // step 2
            'tags' => ['filled', 'array'],
            'tags.*' => ['filled', Rule::exists((new Tag())->getTable(), (new Tag())->getKeyName())],
            // step 3
            'media' => ['filled', 'array'],
            'media.*' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:10240'],
            // step 4
            'contacts' => ['filled', 'array'],
            'contacts.*.id' => ['filled', Rule::exists((new Contact())->getTable(), (new Contact())->getKeyName())],
            'contacts.*.type_id' => ['required', Rule::in(array_keys(Contact::TYPE_IDS))],
            'contacts.*.value' => ['required', new ContactRule($this->contacts)],
            // step 5
            'address' => ['filled', 'array', new AddressRule()],
            'coordinates' => ['filled', 'array', 'size:2'],
            // step 6
            'rooms' => ['filled', 'array'],
            'rooms.*.id' => ['filled', 'integer', Rule::exists((new Room())->getTable(), (new Room())->getKeyName())],
            'rooms.*.name' => ['required', 'string', 'min:2', 'max:255'],
            'rooms.*.description' => ['required', 'string', 'min:10', 'max:1000'],
            'rooms.*.guest_count' => ['required', 'integer', 'min:1', 'max:100'],
            'rooms.*.meals_id' => ['required', Rule::in(array_keys(Room::MEALS_IDS))],
            'rooms.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'rooms.*.price' => ['required', 'integer', 'min:1', 'max:1000000'],
            'rooms.*.price_weekend' => ['required', 'integer', 'min:1', 'max:1000000'],
            'rooms.*.media' => ['filled', 'array'],
            'rooms.*.media.*' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:10240'],
            // step 7
            'lakes' => ['filled', 'array'],
            'lakes.*.id' => ['required', Rule::exists((new Lake())->getTable(), (new Lake())->getKeyName())],
            'lakes.*.distance_shore' => ['required', 'integer', 'min:1', 'max:10000'],
            'custom_lake' => ['nullable', 'string'],
            // step 8
            'conditions' => ['filled', 'string', 'min:5', 'max:2000'],
            'detailed_route' => ['filled', 'string', 'min:5', 'max:2000'],
            'season_id' => ['filled', Rule::in(array_keys(Hotel::SEASON_IDS))],
            'min_days' => ['filled', 'integer', 'min:1', 'max:60'],
            'check_in_hour' => ['filled', 'integer', 'min:0', 'max:23'],
            'check_out_hour' => ['filled', 'integer', 'min:0', 'max:23'],
            // includes inside any step
            'status_id' => ['required', 'integer', Rule::in([
                Hotel::STATUS_ID_DRAFT,
                Hotel::STATUS_ID_UNDER_REVIEW,
            ])],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function validationBeforeUpsert(): void
    {
        $rules = $this->rules();
        if ($this->address) {
            $rules['coordinates'] = ['required', 'array', 'size:2'];
        }

        Validator::make($this->all(), $rules, $this->messages(), $this->attributes())->validate();
    }

    /**
     * @throws ValidationException
     */
    public function validationBeforeModeration(Hotel $hotel, bool $isForShow = false): void
    {
        $hotelOwnerShowResource = HotelOwnerShowResource::make($hotel)->toResponse($this)->getData(true);

        /**
         * We validate images when it uploads -> Here we check media array is not empty for a hotel and a room
         * Here we do not check every image before moderation because every image was validated already
         */
        $rules = $this->rules();
        $rules['address'] = ['required', 'string'];
        $rules['media'] = ['required', 'array'];
        $rules['rooms.*.media'] = ['required', 'array'];
        $rules['contacts.*.value'] = ['required', new ContactRule($hotelOwnerShowResource['data']['contacts'])];

        unset($rules['media.*']);
        unset($rules['rooms.*.media.*']);
        unset($rules['lakes']);

        if ($isForShow) {
            unset($rules['status_id']);
        }

        Validator::make($hotelOwnerShowResource['data'], $rules, $this->messages(), $this->attributes())->validate();
    }

    public function isSendToModeration(): bool
    {
        return $this->getStatusId() === Hotel::STATUS_ID_UNDER_REVIEW;
    }

    private function getStatusId(): int
    {
        return $this->status_id;
    }

    public function prepareRequestBeforeValidation(): void
    {
        if (!$this->address) {
            return;
        }

        $this->merge([
            'address' => collect(json_decode($this->address, true))->mapWithKeys(function (array $item) {
                return [$item['kind'] => $item['name']];
            })->toArray(),
            'coordinates' => json_decode($this->coordinates),
        ]);
    }

    public function attributes(): array
    {
        return [
            'name' => 'Название',
            'type_id' => 'Тип отеля',
            'description' => 'Описание',

            'tags' => 'Теги',
            'tags.*' => 'Тег',

            'media' => 'Фотографии',
            'media.*' => 'Фотография',

            'contacts' => 'Контанты',
            'contacts.*.type_id' => 'Тип',
            'contacts.*.value' => 'Контакт',

            'address' => 'Адрес',
            'coordinates' => 'Координаты',

            'rooms' => 'Номера',
            'rooms.*.name' => 'Название',
            'rooms.*.description' => 'Описание',
            'rooms.*.guest_count' => 'Количество гостей',
            'rooms.*.meals_id' => 'Питание',
            'rooms.*.quantity' => 'Количество',
            'rooms.*.price' => 'Стоимость',
            'rooms.*.price_weekend' => 'Стоимость на выходные',
            'rooms.*.media' => 'Фотографии',
            'rooms.*.media.*' => 'Фотография',

            'lakes' => 'Ближайщие водоёмы',
            'lakes.*.id' => 'Река, море или озеро',
            'lakes.*.distance_shore' => 'Удалённость от берега, м',
            'custom_lake' => 'Свой водоём',

            'conditions' => 'Условия',
            'detailed_route' => 'Подробный маршрут',
            'season_id' => 'Сезон',
            'min_days' => 'Минимальное количество дней',
            'check_in_hour' => 'Час заезда',
            'check_out_hour' => 'Час выезда',
        ];
    }
}
