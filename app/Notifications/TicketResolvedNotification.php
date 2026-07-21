<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;
use App\Models\Ticket;

class TicketResolvedNotification extends Notification implements ShouldQueue
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
            ->success()
            ->subject('✅ Ticket Resolved: ' . $this->ticket->ticket_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Good news! Your ticket (' . $this->ticket->ticket_number . ') has been marked as Resolved.')
            ->action('View Ticket', url('/app/tickets/' . $this->ticket->ticket_number))
            ->line('If you are not satisfied, you can reopen the ticket from your dashboard. - Queue Goblin');
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Ticket Resolved! 🎉')
            ->body('Your ticket ' . $this->ticket->ticket_number . ' has been resolved.')
            ->success()
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
