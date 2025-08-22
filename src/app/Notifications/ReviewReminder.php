<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

class ReviewReminder extends Notification
{
    use Queueable;

    public function __construct(public Reservation $reservation)
    {
        //
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $shopName = $this->reservation->shop->name ?? 'ご来店店舗';
        $url = route('user.reviews.create', $this->reservation);

        return (new MailMessage)
            ->subject("{$shopName} のご来店ありがとうございます。レビューのお願い")
            ->greeting("{$notifiable->name} 様")
            ->line('本日のご来店ありがとうございます。よろしければレビューのご協力をお願いします。')
            ->action('レビューを書く', $url)
            ->line('いただいたご意見はサービス向上に活用させていただきます。');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'レビューのお願い',
            'message' => ($this->reservation->shop->name ?? 'ご来店店舗') . ' のレビューをお願いします。',
            'reservation_id' => $this->reservation->id,
            'shop_id' => $this->reservation->shop_id,
            'url' => route('user.reviews.create', $this->reservation),
        ];
    }
}
