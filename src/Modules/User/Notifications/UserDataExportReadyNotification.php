<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Notifications;

use BilliftyResumeSDK\SharedResources\Modules\User\Models\UserDataExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserDataExportReadyNotification extends Notification
{
    use Queueable;

    public function __construct(public UserDataExport $export, public string $downloadUrl) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your data export is ready')
            ->line('Your data export is ready to download.')
            ->action('Download export', $this->downloadUrl)
            ->line('This link will expire at: ' . optional($this->export->expires_at)->toDayDateTimeString());
    }
}
