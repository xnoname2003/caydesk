<?php

namespace App\Http\Controllers\Api\TicketActions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Http\Requests\StoreCommentRequest;
use App\Notifications\TicketCommentedNotification;

class SubmitTicketReplyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreCommentRequest $request, $ticket_number)
    {
        $ticket = Ticket::where('ticket_number', $ticket_number)->firstOrFail();
        $validated = $request->validated();
        $user = $request->user();

        $comment = $ticket->comments()->create([
            'user_id' => $user->id,
            'content' => $validated['content'],
            'is_internal' => $validated['is_internal'] ?? 0,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('comment-attachments', 'public');
                $comment->attachments()->create([
                    'uploaded_by' => $user->id,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name' => basename($path),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        if (is_null($ticket->first_responded_at) && !$user->hasRole('customer')) {
            $ticket->update(['first_responded_at' => now()]);
        }

        $isInternal = $validated['is_internal'] ?? 0;

        if (!$isInternal && $ticket->created_by !== $user->id) {
            $ticket->creator->notify(new TicketCommentedNotification($comment));
        }

        if ($ticket->assigned_agent_id && $ticket->assigned_agent_id !== $user->id) {
            $ticket->assignedAgent->notify(new TicketCommentedNotification($comment));
        }

        return response()->json([
            'message' => 'Reply sent successfully!',
            'data' => $comment->load('user')
        ], 201);
    }
}
