<?php

namespace App\Filament\Pages\Tickets\Actions;

use App\Models\Ticket;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Redirect;

class DeleteTicketAction
{
    public static function make(Ticket $ticket): Action
    {
        return Action::make('deleteTicket')
            ->label('Delete Ticket')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Delete this ticket?')
            ->modalDescription('Are you sure you\'d like to delete this ticket? This action cannot be undone.')
            ->visible(fn () => auth()->user()->hasRole('administrator'))
            ->action(function () use ($ticket) {
                $ticket->delete();
                return Redirect::to('/app/tickets'); 
            })
            ->successNotificationTitle('Ticket deleted successfully!');
    }
}