<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreatedNotification;
use Illuminate\Support\Facades\Log;
use App\Notifications\TicketResolvedNotification;
use App\Services\TicketStatusService;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        $notifiableUsers = User::role(['administrator', 'supervisor'])->get();

        foreach ($notifiableUsers as $user) {
            $user->notify(new TicketCreatedNotification($ticket));
        }
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        if ($ticket->wasChanged('status')) {
            
            if ($ticket->status === TicketStatusService::STATUS_RESOLVED) {
                
                if (is_null($ticket->resolved_at)) {
                    $ticket->updateQuietly(['resolved_at' => now()]);
                }
                
                $ticket->creator->notify(new TicketResolvedNotification($ticket));
            }
            
            if ($ticket->status === TicketStatusService::STATUS_CLOSED) {
                if (is_null($ticket->closed_at)) {
                    $ticket->updateQuietly(['closed_at' => now()]);
                }
            }

            if ($ticket->status === TicketStatusService::STATUS_REOPENED) {
                $ticket->updateQuietly([
                    'resolved_at' => null,
                    'closed_at' => null,
                ]);
            }
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        //
    }
}
