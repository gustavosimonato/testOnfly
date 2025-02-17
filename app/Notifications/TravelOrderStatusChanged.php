<?php

namespace App\Notifications;

use App\Models\TravelOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TravelOrderStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $travelRequest;

    public function __construct(TravelOrder $travelRequest)
    {
        $this->travelRequest = $travelRequest;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $status = ucfirst($this->travelRequest->status);

        return (new MailMessage)
            ->subject("Travel Request {$status}")
            ->view('emails.travel_order_status_changed', [
                'travelRequest' => $this->travelRequest,
                'status' => $status,
            ]);

    }
}
