<?php

namespace App\Filament\Pages\Tickets\Actions;

use App\Models\Ticket;
use App\Notifications\TicketCommentedNotification;
use Filament\Notifications\Notification as FilamentUI;

class SubmitReplyAction
{
    public static function execute(Ticket $ticket, array $data): void
    {
        $comment = $ticket->comments()->create([
            'user_id' => auth()->id(),
            'content' => $data['content'],
            'is_internal' => $data['is_internal'] ?? 0,
        ]);

        $attachments = $data['file_attachments'] ?? [];
        foreach ($attachments as $jsonString) {
            $meta = json_decode($jsonString, true);

            if ($meta && isset($meta['path'])) {
                $comment->attachments()->create([
                    'uploaded_by' => auth()->id(),
                    'original_name' => $meta['original_name'],
                    'stored_name' => basename($meta['path']),
                    'path' => $meta['path'],
                    'mime_type' => $meta['mime_type'],
                    'size' => $meta['size'],
                ]);
            }
        }

        if (is_null($ticket->first_responded_at) && ! auth()->user()->hasRole('customer')) {
            $ticket->update([
                'first_responded_at' => now(),
            ]);
        }

        $commenterId = auth()->id();
        $isInternal = $data['is_internal'] ?? 0;

        if (! $isInternal && $ticket->created_by !== $commenterId) {
            $ticket->creator->notify(new TicketCommentedNotification($comment));
        }

        if ($ticket->assigned_agent_id && $ticket->assigned_agent_id !== $commenterId) {
            $ticket->assignedAgent->notify(new TicketCommentedNotification($comment));
        }

        $ticket->load('comments.user', 'comments.attachments');

        FilamentUI::make()
            ->title('Reply sent successfully')
            ->success()
            ->send();
    }
}
