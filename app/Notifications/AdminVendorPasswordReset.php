<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class AdminVendorPasswordReset extends Notification
{
    use Queueable;

    protected $vendor;

    public function __construct(User $vendor)
    {
        $this->vendor = $vendor;
    }

    public function via($notifiable)
    {
        return ['database']; // For in-app notifications
    }

    public function toArray($notifiable)
    {
        return [
            'text' => 'Vendor ' . $this->vendor->name . ' has requested a password reset.',
            'vendor_id' => $this->vendor->id,
        ];
    }
}