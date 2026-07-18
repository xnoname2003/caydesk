<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;
use Filament\Notifications\Notification as FilamentNotification;

class SlaOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Ticket $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
       return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->error()
                    ->subject('⚠️ SLA OVERDUE: ' . $this->ticket->ticket_number)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('The SLA for Ticket ' . $this->ticket->ticket_number . ' has been breached.')
                    ->line('Priority: ' . strtoupper($this->ticket->priority?->name ?? 'N/A'))
                    ->action('Take Immediate Action', url('/app/tickets/' . $this->ticket->id))
                    ->line('The ticket status has been automatically escalated. - Queue Goblin');
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('SLA Overdue! 🔥')
            ->body('Ticket ' . $this->ticket->ticket_number . ' is on fire.')
            ->danger()
            ->getDatabaseMessage();
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
