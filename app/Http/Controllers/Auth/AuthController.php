<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\SmscSendException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterCodeRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Services\UserService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    /**
     * @throws SmscSendException
     */
    public function login(RegisterRequest $registerRequest): JsonResponse
    {
        $userService = UserService::create();
        $userService->sendConfirmationCode($registerRequest->phone);

        return response()->json([
            'message' => 'Код выслан вам на телефон.',
            'data' => [
                'phone' => $registerRequest->phone,
            ],
        ]);
    }

    public function inputPrivateCode(RegisterCodeRequest $codeRequest): JsonResponse
    {
        if (!$user = User::findByPhone($codeRequest->phone)) {
            $user = new User();
            $user->role_id = User::ROLE_ID_CLIENT;
            $user->phone = $codeRequest->phone;
            $user->save();
        }

        Cache::forget($codeRequest->phone);

        Auth::login($user);
        $codeRequest->session()->regenerate();

        return response()->json([
            'message' => 'Вы успешно залогинились.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Вы успешно разлогинились.',
        ]);
    }
}
