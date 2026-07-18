<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreatedNotification;
use Illuminate\Support\Facades\Log;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        $admins = User::role('administrator')->get();
        
        foreach ($admins as $admin) {
            $admin->notify(new TicketCreatedNotification($ticket));
        }
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        //
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
