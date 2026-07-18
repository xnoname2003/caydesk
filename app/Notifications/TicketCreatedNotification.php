<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

use Filament\Notifications\Notification as FilamentNotification;

class TicketCreatedNotification extends Notification implements ShouldQueue
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
            ->subject('New Ticket Created: '.$this->ticket->ticket_number)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new support ticket has been created by '.$this->ticket->creator->name.'.')
            ->line('Priority: '.strtoupper($this->ticket->priority->name))
            ->action('View Ticket', url('/app/tickets/'.$this->ticket->ticket_number))
            ->line('Thank you for keeping the system running. - Queue Goblin');
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

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('New Ticket Created')
            ->body('Ticket ' . $this->ticket->ticket_number . ' needs your attention.')
            ->success()
            ->getDatabaseMessage();
    }
}
