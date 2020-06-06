<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    public $token; // 追加
    protected $title = 'パスワードリセット 通知'; // 追加

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        // $tokenを追加
        $this->token = $token; // $this->token = $tokenを追加
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $toplevelURL = route('home');
        $url = $toplevelURL . "/password/reset/{$this->token}";

        return (new MailMessage())
            ->from('itsup-info@shimanamisan.com', config('app.name'))
            ->subject($this->title)
            ->line('下のボタンをクリックしてパスワードを再設定してください。')
            ->action('パスワード再設定', $url)
            ->line(
                'もし心当たりがない場合は、本メッセージは破棄してください。'
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
                //
            ];
    }
}
