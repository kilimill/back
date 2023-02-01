<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\SmscSendException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\User\UserChangePhoneRequest;
use App\Http\Requests\Api\v1\User\UserUpdatePartnerRequest;
use App\Http\Requests\Api\v1\User\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Http\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserProfileController extends Controller
{
    public function index(): JsonResource
    {
        return UserResource::make(auth_user_or_fail());
    }

    /**
     * @throws SmscSendException
     */
    public function update(UserUpdateRequest $userUpdateRequest): JsonResponse
    {
        $user = auth_user_or_fail();
        $user->name = $userUpdateRequest->name;
        $user->email = $userUpdateRequest->email;

        if ($userUpdateRequest->avatar) {
            $user->clearMediaCollection('avatars')
                 ->addMediaFromRequest('avatar')
                 ->toMediaCollection('avatars');
        }

        $user->save();

        if ($user->phone !== $userUpdateRequest->phone) {
            $userService = UserService::create();
            $userService->sendConfirmationCode($userUpdateRequest->phone);

            return response()->json([
                'message' => 'Код подтверждения отправлен вам на телефон.',
                'data' => [
                    'phone' => $userUpdateRequest->phone,
                ],
            ]);
        }

        return response()->json([
            'message' => 'Данные успешно сохранены.',
            'data' => UserResource::make(auth_user_or_fail()),
        ]);
    }

    public function inputChangePhoneCode(UserChangePhoneRequest $userChangePhoneRequest): JsonResponse
    {
        $user = auth_user_or_fail();
        $user->phone = $userChangePhoneRequest->phone;
        $user->save();

        Cache::forget($userChangePhoneRequest->phone);

        return response()->json([
            'message' => 'Телефон успешно изменен.',
            'data' => UserResource::make(auth_user_or_fail()),
        ]);
    }

    public function updatePartner(UserUpdatePartnerRequest $userUpdatePartnerRequest): JsonResponse
    {
        $user = auth_user_or_fail();
        $user->first_name_owner = $userUpdatePartnerRequest->first_name_owner;
        $user->last_name_owner = $userUpdatePartnerRequest->last_name_owner;
        $user->phone_owner = $userUpdatePartnerRequest->phone_owner;
        $user->email_owner = $userUpdatePartnerRequest->email_owner;
        $user->inn_owner = $userUpdatePartnerRequest->inn_owner;
        $user->kpp_owner = $userUpdatePartnerRequest->kpp_owner ?? null;
        $user->legal_form_id = $userUpdatePartnerRequest->legal_form_id;
        $user->save();

        return response()->json([
            'message' => 'Данные успешно сохранены.',
            'data' => UserResource::make(auth_user_or_fail()),
        ]);
    }
}
