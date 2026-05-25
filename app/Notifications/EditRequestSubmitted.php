<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ReadingEditRequest;

class EditRequestSubmitted extends Notification
{
    use Queueable;

    public $editRequest;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(ReadingEditRequest $editRequest)
    {
        $this->editRequest = $editRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
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
            'request_id' => $this->editRequest->id,
            'stall_number' => $this->editRequest->utilityReading->stall->table_number,
            'reason' => $this->editRequest->reason,
            'requested_by' => $this->editRequest->requested_by,
        ];
    }
}
