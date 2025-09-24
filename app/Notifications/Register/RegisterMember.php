<?php

namespace App\Notifications\Register;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RegisterMember extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $user;
    protected $password;
    public function __construct(User $user,$password)
    {

        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail','database'];
    }

    public function toMail($notifiable)
    {

        return (new MailMessage)
                    ->line('Welcome to ITCSTC !')
                    ->line('Password: '.$this->password)
                    ->line('Email: '.$this->user->email)
                    ->action('Login Here', url(env('PORTAL_PROD_URL')))
                    ->line('Thank you for joining ITCSTC !');
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
            'message' => 'testing event'
        ];
    }
}
