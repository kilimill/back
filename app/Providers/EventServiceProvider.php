<?php

namespace App\Providers;


use App\Models\Hotel;
use App\Observers\AuditObserver;
use App\Observers\HotelObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use OwenIt\Auditing\Models\Audit;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        //
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        $this->registerObservers();
    }

    private function registerObservers(): void
    {
        Hotel::observe(HotelObserver::class);
        Audit::observe(AuditObserver::class);
    }
}
