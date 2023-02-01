<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\HowRegion;
use App\Nova\Metrics\NewHotels;
use App\Nova\Metrics\NewBookings;
use App\Nova\Metrics\News;
use App\Nova\Metrics\NewUsers;
use App\Nova\Metrics\WhoUsers;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Dashboards\Main as Dashboard;
use Laravel\Nova\Element;

class Main extends Dashboard
{

    public function label()
    {
        return __('Главная');
    }

    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            new NewUsers(),
            new NewBookings(),
            new NewHotels(),
            new WhoUsers(),
            new News(),
            new HowRegion()
        ];
    }
}
