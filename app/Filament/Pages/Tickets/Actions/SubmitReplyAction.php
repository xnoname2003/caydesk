<?php

namespace App\Filament\Pages\Tickets\Actions;

use App\Models\Ticket;
use Filament\Notifications\Notification;

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

        if (is_null($ticket->first_responded_at) && !auth()->user()->hasRole('customer')) {
            $ticket->update([
                'first_responded_at' => now()
            ]);
        }

        $ticket->load('comments.user', 'comments.attachments');

        Notification::make()
            ->title('Reply sent successfully')
            ->success()
            ->send();
    }
}