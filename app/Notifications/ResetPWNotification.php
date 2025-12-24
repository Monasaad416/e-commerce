<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPWNotification extends Notification
{
    use Queueable;

    public $code;
    public function __construct($code)
    {
        $this->code = $code;
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
            ->subject(__('Reset Password'))
            ->line(__('We received a password reset request for your account.'))
            ->line(__('Your verification code is:'))
            ->line($this->code)
            ->line(__('This code will expire in 15 minutes.'));
    }


}
