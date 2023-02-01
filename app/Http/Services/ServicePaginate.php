<?php

namespace App\Http\Services;

use Illuminate\Support\Collection;

trait ServicePaginate
{
    private int $page;
    private int $perPage;

    private function setPagination(?int $page, ?int $perPage): void
    {
        $this->page = $page ?? 1;
        $this->perPage = $perPage ?? config('nollo.per_page');
    }

    public function nextPage(Collection $collection): ?int
    {
        if ($collection->count() > $this->perPage) {
            $collection->pop();

            return  $this->page + 1;
        }

        return null;
    }
}
