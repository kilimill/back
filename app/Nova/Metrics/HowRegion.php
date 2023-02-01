<?php

namespace App\Nova\Metrics;

use App\Models\Hotel;
use App\Models\Region;
use App\Models\Room;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class HowRegion extends Partition
{
    public function name(): string
    {
        return 'Отели по регионам';
    }

    public function calculate(NovaRequest $request): mixed
    {
        return $this->count($request, Hotel::query()->whereNotNull('region_id'), 'region_id')
            ->label(fn ($value) => match ($value) {
                null => 'None',
                default => Region::find($value)->name,
            });
    }
    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'regions';
    }
}
