<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;

class TicketAssignedNotification extends Notification implements ShouldQueue
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
            ->subject('🎯 New Ticket Assigned: ' . $this->ticket->ticket_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have been assigned to handle a new ticket (' . $this->ticket->ticket_number . ').')
            ->line('Priority: ' . strtoupper($this->ticket->priority?->name ?? 'N/A'))
            ->action('View Ticket', url('/app/tickets/' . $this->ticket->ticket_number))
            ->line('Please review and process it according to the SLA. - Queue Goblin');
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Ticket Assigned! 🎯')
            ->body('You have been assigned to ticket ' . $this->ticket->ticket_number)
            ->info()
            ->actions([
                Action::make('view')
                    ->label('View Ticket')
                    ->button()
                    ->url(url('/app/tickets/' . $this->ticket->ticket_number))
                    ->markAsRead(),
            ])
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