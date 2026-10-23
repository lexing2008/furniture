<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * The letter with the link that confirms the registration. The link is signed for the host the user registered on,
 * so it opens in the same region (berlin.furniture.com).
 */
class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("Подтвердите регистрацию на {$appName}")
            ->view(['emails.verify-email', 'emails.verify-email-text'], [
                'url' => $this->verificationUrl($notifiable),
                'name' => $notifiable->name,
                'company' => $notifiable->company?->display_name,
                'appName' => $appName,
                'expire' => (int) config('auth.verification.expire', 60),
            ]);
    }
}
