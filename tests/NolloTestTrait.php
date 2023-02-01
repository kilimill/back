<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait NolloTestTrait
{
    protected function userLogin(User $user): void
    {
        $this->actingAs($user);
    }

    protected function userLogOut(): void
    {
        Auth::guard('web')->logout();
    }
}
