<?php

namespace App\Notifications\UserVerification;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable) : array
    {
        return ['mail'];
    }

    public function toMail($notifiable) : MailMessage
    {
        return (new MailMessage)
            ->subject('Verify Email Address')
            ->line('Click the button below to verify your email address.')
            ->action('Verify Email Address', $this->url);

    }


    public function toArray($notifiable) : array
    {
        return [
            //
        ];
    }
}
