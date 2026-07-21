<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Comment;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;

class TicketCommentedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Comment $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
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
            ->subject('💬 New Comment on Ticket: ' . $this->comment->ticket->ticket_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->comment->user->name . ' has added a new comment to ticket ' . $this->comment->ticket->ticket_number . '.')
            ->action('View Comment', url('/app/tickets/' . $this->comment->ticket->ticket_number))
            ->line('Thank you! - Queue Goblin');
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('New Comment on Ticket! 💬')
            ->body($this->comment->user->name . ' commented on ' . $this->comment->ticket->ticket_number)
            ->info()
            ->actions([
                Action::make('view')
                    ->label('View Ticket')
                    ->button()
                    ->url(url('/app/tickets/' . $this->comment->ticket->ticket_number))
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
