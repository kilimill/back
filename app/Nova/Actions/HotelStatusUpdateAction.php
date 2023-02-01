<?php

namespace App\Nova\Actions;

use App\Models\Hotel;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class HotelStatusUpdateAction extends Action
{
    use InteractsWithQueue, Queueable;

    private int $statusId;
    private string $actionName;

    public function __construct($statusId, string $actionName)
    {
        $this->statusId = $statusId;
        $this->actionName = $actionName;
    }

    public function name(): string
    {
        return $this->actionName;
    }

    public function handle(ActionFields $fields, Collection $models): array
    {
        try {
            $models->each(function (Hotel $hotel) {
                $hotel->status_id = $this->statusId;
                $hotel->save();
            });
        } catch (\Exception $e) {
            return Action::danger('Что-то пошло не так. Попробуйте позже.');
        }

        return Action::message('Выбраные отели обновлены успешно.');
    }
}
