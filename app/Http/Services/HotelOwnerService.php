<?php

namespace App\Http\Services;

use App\Exceptions\ApiHotelValidationException;
use App\Exceptions\ApiLogicException;
use App\Http\Adapters\AddressAdapter;
use App\Http\Requests\Api\v1\Hotel\HotelUpsertRequest;
use App\Models\Contact;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class HotelOwnerService
{
    use ServiceInstance;

    /**
     * @throws ApiHotelValidationException
     */
    public function validationBeforeUpsert(HotelUpsertRequest $hotelUpsertRequest): void
    {
        try {
            $hotelUpsertRequest->validationBeforeUpsert();
        } catch (ValidationException $e) {
            throw (new ApiHotelValidationException())->fromLaravel($e);
        }
    }

    /**
     * @throws ApiLogicException
     */
    public function handle(?Hotel $hotel, HotelUpsertRequest $hotelUpsertRequest): Hotel
    {
        DB::beginTransaction();
        try {
            $hotel = $this->upsert($hotel, $hotelUpsertRequest);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Cannot upsert hotel: '. $exception->getMessage(), [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            throw new ApiLogicException('Ошибка при сохранении данных, попробуйте снова или обратитесь в поддержку.');
        }

        DB::commit();

        return $hotel;
    }

    /**
     * @throws ApiHotelValidationException
     */
    public function validationBeforeModeration(Hotel $hotel, HotelUpsertRequest $hotelUpsertRequest): ?ApiHotelValidationException
    {
        try {
            $hotelUpsertRequest->validationBeforeModeration($hotel);
        } catch (ValidationException $e) {
            $hotelValidationException = (new ApiHotelValidationException())->fromLaravel($e);

            if ($hotelUpsertRequest->isSendToModeration()) {
                throw $hotelValidationException;
            }

            return $hotelValidationException;
        }

        if ($hotelUpsertRequest->isSendToModeration()) {
            $this->sendToReview($hotel);
        }

        return null;
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    private function upsert(?Hotel $hotel, HotelUpsertRequest $hotelUpsertRequest): Hotel
    {
        $user = auth_user_or_fail();
        $address = $hotelUpsertRequest->address ? AddressAdapter::transform($hotelUpsertRequest->address) : null;

        if (!$hotel) {
            $hotel = new Hotel();
        }

        $hotel->status_id = Hotel::STATUS_ID_DRAFT;

        if ($hotelUpsertRequest->type_id) {
            $hotel->type_id = $hotelUpsertRequest->type_id;
        }
        if ($hotelUpsertRequest->name) {
            $hotel->name = $hotelUpsertRequest->name;
        }
        if ($hotelUpsertRequest->description) {
            $hotel->description = $hotelUpsertRequest->description;
        }
        if ($address?->getCountryId()) {
            $hotel->country_id = $address->getCountryId();
        }
        if ($address?->getRegionId()) {
            $hotel->region_id = $address->getRegionId();
        }
        if ($address?->getCityId()) {
            $hotel->city_id = $address->getCityId();
        }
        if ($address?->getAddress()) {
            $hotel->address = $address->getAddress();
        }
        if ($hotelUpsertRequest->coordinates) {
            $hotel->coordinates = implode(',', $hotelUpsertRequest->coordinates);
        }

        if ($hotelUpsertRequest->detailed_route) {
            $hotel->detailed_route = $hotelUpsertRequest->detailed_route;
        }
        if ($hotelUpsertRequest->conditions) {
            $hotel->conditions = $hotelUpsertRequest->conditions;
        }
        if ($hotelUpsertRequest->season_id) {
            $hotel->season_id = $hotelUpsertRequest->season_id;
        }
        if ($hotelUpsertRequest->min_days) {
            $hotel->min_days = $hotelUpsertRequest->min_days;
        }
        // value can be '0'
        if ($hotelUpsertRequest->check_in_hour !== null) {
            $hotel->check_in_hour = $hotelUpsertRequest->check_in_hour;
        }
        // value can be '0'
        if ($hotelUpsertRequest->check_out_hour !== null) {
            $hotel->check_out_hour = $hotelUpsertRequest->check_out_hour;
        }
        if ($hotelUpsertRequest->custom_lake) {
            $hotel->custom_lake = $hotelUpsertRequest->custom_lake;
        }

        $hotel->user_id = $user->getKey();
        $hotel->created_at = $hotel->created_at ?? now();
        $hotel->updated_at = now();
        $hotel->save();

        if ($tags = $hotelUpsertRequest->tags) {
            $hotel->tags()->sync($tags);
        }

        if ($contacts = $hotelUpsertRequest->contacts) {
            $this->addContacts($hotel, $contacts);
        }

        if ($media = $hotelUpsertRequest->media) {
            $this->addMedia($hotel, $media);
        }

        if ($lakes = $hotelUpsertRequest->lakes) {
            $hotel->lakes()->sync(collect($lakes)->mapWithKeys(function (array $lake) {
                return [
                    $lake['id'] => ['distance_shore' => $lake['distance_shore']],
                ];
            }));
        }

        if ($rooms = $hotelUpsertRequest->rooms) {
            $this->addRooms($hotel, $rooms);
        }

        if ($user->role_id === User::ROLE_ID_CLIENT) {
            $user->role_id = User::ROLE_ID_OWNER;
            $user->save();
        }

        return $hotel;
    }

    private function sendToReview(Hotel $hotel): void
    {
        $hotel->status_id = Hotel::STATUS_ID_UNDER_REVIEW;
        $hotel->save();
    }

    private function addContacts(Hotel $hotel, array $contacts): void
    {
        $contacts = collect($contacts)->map(function (array $item) use ($hotel) {
            return [
                'id' => $item['id'] ?? null,
                'hotel_id' => $hotel->getKey(),
                'type_id' => $item['type_id'],
                'value' => $item['value'],
            ];
        })->toArray();

        Contact::query()->upsert($contacts, ['id'], ['type_id', 'value']);
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    private function addMedia(Hotel $hotel, array $media): void
    {
        collect($media)->each(function (UploadedFile $file) use ($hotel) {
            $hotel->addMedia($file)->preservingOriginal()
                ->withCustomProperties(['preview' => false])
                ->toMediaCollection('media');
        });
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    private function addRooms(Hotel $hotel, array $rooms): void
    {
        collect($rooms)->each(function (array $roomData)  use ($hotel) {
            // Upsert a first room to a group
            $room = $this->upsertRoom($hotel, $roomData);
            // Force delete grouped rooms except the first room in the group
            $hotel->rooms()->where('group_id', $room->getKey())
                ->whereNot('id', $room->getKey())
                ->forceDelete();

            // Create child rooms if the request has the quantity value > 1
            $quantity = $roomData['quantity'];
            if ($quantity > 1) {
                for ($i = 1; $i < $quantity; $i++) {
                    // To create a new child room we should not have room id
                    $roomData['id'] = null;
                    // To create a new child room we should have parent id
                    $roomData['group_id'] = $room->getKey();
                    $this->upsertRoom($hotel, $roomData);
                }
            }
        });
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    private function upsertRoom(Hotel $hotel, array $roomData): Room
    {

        $room = new Room();

        if ($id = $roomData['id'] ?? null) {
            // If room id exists in the request, it needs to check if the room is attached to the hotel
            $room = Room::query()->where('hotel_id', $hotel->getKey())->find($id);

            // If for some reasons request contains wrong room id for the hotel it needs to crate a new room model instance
            if (!$room) {
                $room = new Room();
            }
        }

        $room->hotel_id = $hotel->getKey();
        $room->group_id = $roomData['group_id'] ?? null;
        $room->name = $roomData['name'];
        $room->description = $roomData['description'];
        $room->guest_count = $roomData['guest_count'];
        $room->meals_id = $roomData['meals_id'];
        $room->price = $roomData['price'];
        $room->price_weekend = $roomData['price_weekend'];
        $room->save();

        if (!$room->group_id) {
            $room->group_id = $room->getKey();
            $room->save();
        }


        if ($media = $roomData['media'] ?? null) {
            $room->clearMediaCollection('media');

            $preview = collect($media)->first();
            $room->addMedia($preview)->preservingOriginal()
                ->withCustomProperties(['preview' => true])
                ->toMediaCollection('media');
        }

        return $room;
    }
}
