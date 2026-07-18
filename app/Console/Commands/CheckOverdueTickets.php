<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\SlaOverdueNotification;
use App\Services\TicketStatusService;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Command;

#[Description('Command description')]

class CheckOverdueTickets extends Command
{
    protected $signature = 'tickets:check-overdue';

    protected $description = 'Check for unresolved tickets past their SLA due date and trigger notifications.';

    public function handle()
    {
        $this->info('Queue Goblin is checking for overdue tickets...');

        $overdueTickets = Ticket::whereNotIn('status', [
            TicketStatusService::STATUS_RESOLVED,
            TicketStatusService::STATUS_CLOSED,
        ])
            ->where('due_at', '<', Carbon::now())
            ->get();

        $supervisors = User::role(['supervisor', 'administrator'])->get();

        foreach ($overdueTickets as $ticket) {
            foreach ($supervisors as $spv) {
                $spv->notify(new SlaOverdueNotification($ticket));
            }

            if ($ticket->assignedAgent) {
                $ticket->assignedAgent->notify(new SlaOverdueNotification($ticket));
            }

            if (TicketStatusService::isValidTransition($ticket->status, TicketStatusService::STATUS_ESCALATED)) {
                
                $ticket->update(['status' => TicketStatusService::STATUS_ESCALATED]);
                $this->info("Ticket {$ticket->ticket_number} escalated successfully.");
                
            } else {
                $this->warn("Ticket {$ticket->ticket_number} cannot be escalated from status {$ticket->status}.");
            }
        }

        $this->info('Check complete. Found '.$overdueTickets->count().' overdue tickets.');
    }
}
