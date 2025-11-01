<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeacherApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $teacher;
    protected $password;

    /**
     * Create a new notification instance.
     */
    public function __construct($teacher, $password)
    {
        $this->teacher = $teacher;
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Teacher Account Approved - UCSH Timetable System')
                    ->greeting('Dear ' . $this->teacher->name . ',')
                    ->line('Congratulations! Your teacher application has been approved.')
                    ->line('Your account has been created with the following credentials:')
                    ->line('**Username:** ' . $notifiable->username)
                    ->line('**Password:** ' . $this->password)
                    ->line('**Email:** ' . $notifiable->email)
                    ->action('Login to Your Account', url('/'))
                    ->line('Please change your password after first login for security.')
                    ->line('Welcome to the UCSH Timetable Management System!')
                    ->salutation('Best regards, UCSH Administration');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'teacher_id' => $this->teacher->id,
            'message' => 'Your teacher application has been approved.',
        ];
    }
}
