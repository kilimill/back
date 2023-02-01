<?php

namespace App\Notifications;

use App\Models\Hotel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Laravel\Nova\Notifications\NovaChannel;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;

class NewHotelNotification extends Notification
{
    use Queueable;

    public Hotel $hotel;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Hotel $hotel)
    {
        $this->hotel = $hotel;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return [NovaChannel::class];
    }

    public function toNova(): NovaNotification
    {
        return (new NovaNotification)
            ->message('Добавлен новый отель')
            ->action('Посмотреть', URL::remote('/nova/resources/hotels/'. $this->hotel->getKey()))
            ->icon('download')
            ->type('info');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            //
        ];
    }
}
