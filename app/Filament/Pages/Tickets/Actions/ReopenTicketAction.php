<?php

namespace App\Filament\Pages\Tickets\Actions;

use App\Models\Ticket;
use App\Services\TicketStatusService;
use Filament\Actions\Action;

class ReopenTicketAction
{
    public static function make(Ticket $ticket): Action
    {
        return Action::make('reopenTicket')
            ->label('Reopen Ticket')
            ->button()
            ->color('warning')
            ->icon('heroicon-m-arrow-path')
            ->requiresConfirmation()
            ->modalHeading('Reopen Ticket')
            ->modalDescription('Is the issue still occurring? Reopening this ticket will send it back to our support team for further investigation.')
            ->visible(fn () => auth()->id() === $ticket->created_by && !auth()->user()->hasAnyRole(['administrator', 'supervisor']) && app(TicketStatusService::class)->isValidTransition($ticket->status, TicketStatusService::STATUS_REOPENED))
            ->action(function () use ($ticket) {
                $ticket->update([
                    'status' => TicketStatusService::STATUS_REOPENED,
                ]);
            })
            ->successNotificationTitle('Ticket reopened successfully!');
    }
}