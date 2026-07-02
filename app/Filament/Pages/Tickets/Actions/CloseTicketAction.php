<?php

namespace App\Filament\Pages\Tickets\Actions;

use App\Models\Ticket;
use App\Services\TicketStatusService;
use Filament\Actions\Action;

class CloseTicketAction
{
    public static function make(Ticket $ticket): Action
    {
        return Action::make('closeTicket')
            ->label('Mark as Closed')
            ->button()
            ->color('success')
            ->icon('heroicon-m-check-circle')
            ->requiresConfirmation()
            ->modalHeading('Close Ticket')
            ->modalDescription('Are you sure you want to close this ticket? This indicates that your issue has been completely resolved.')
            ->visible(fn () => auth()->user()->hasRole('customer') && app(TicketStatusService::class)->isValidTransition($ticket->status, TicketStatusService::STATUS_CLOSED))
            ->action(function () use ($ticket) {
                $ticket->update([
                    'status' => TicketStatusService::STATUS_CLOSED,
                    'closed_at' => now(),
                ]);
                $ticket->load('assignedAgent');
            })
            ->successNotificationTitle('Ticket closed successfully!');
    }
}