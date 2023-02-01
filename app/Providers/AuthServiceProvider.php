<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\ConfirmationCode;
use App\Policies\BookingPolicy;
use App\Policies\HotelPolicy;
use App\Policies\ConfirmationCodePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Hotel::class => HotelPolicy::class,
        Booking::class => BookingPolicy::class,
        ConfirmationCode::class => ConfirmationCodePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
