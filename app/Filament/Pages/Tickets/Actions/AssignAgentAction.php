<?php

namespace App\Filament\Pages\Tickets\Actions;

use App\Models\Ticket;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use App\Services\TicketStatusService;
use Illuminate\Database\Eloquent\Builder;

class AssignAgentAction
{
    public static function make(Ticket $ticket): Action
    {
        return Action::make('assignAgent')
            ->label('Change Assignee')
            ->button()
            ->outlined()
            ->icon('heroicon-m-user-plus')
            ->visible(fn() => auth()->user()->hasAnyRole(['administrator', 'supervisor']))
            ->form([
                Select::make('assigned_agent_id')
                    ->label('Assign To')
                    ->placeholder('Select Assignee...')
                    ->options(function () {
                        $query = User::role([
                            'agent',
                            'supervisor',
                            'administrator'
                        ]);
                        if (auth()->user()->hasRole('supervisor')) {
                            $query->where(function (Builder $q) {
                                $q->where('team_id', auth()->user()->team_id)
                                    ->orWhere('id', auth()->user()->id);
                            });
                        }
                        return $query->orderBy('name')->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->default($ticket->assigned_agent_id)
            ])
            ->action(function (array $data) use ($ticket) {
                $updates = [
                    'assigned_agent_id' => $data['assigned_agent_id'],
                ];

                $statusService = app(TicketStatusService::class);
                if ($statusService->isValidTransition($ticket->status, TicketStatusService::STATUS_ASSIGNED)) {
                    $updates['status'] = TicketStatusService::STATUS_ASSIGNED;
                }

                $ticket->update($updates);
                $ticket->load('assignedAgent');
            })
            ->successNotificationTitle('Agent assigned successfully!');
    }
}