<?php

namespace App\Filament\Pages\Tickets\Actions;

use App\Models\Ticket;
use App\Models\Priority;
use App\Models\SlaRule;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

class UpdatePriorityAction
{
    public static function make(Ticket $ticket): Action
    {
        return Action::make('updatePriority')
            ->label('Change Priority')
            ->button()
            ->outlined()
            ->icon('heroicon-m-flag')
            ->visible(fn () => auth()->user()->hasAnyRole(['administrator', 'supervisor']))
            ->form([
                Select::make('priority_id')
                    ->label('Ticket Priority')
                    ->options(Priority::pluck('name', 'id'))
                    ->required()
                    ->default($ticket->priority_id)
            ])
            ->action(function (array $data) use ($ticket) {
                $newPriorityId = $data['priority_id'];
                $slaRule = SlaRule::where('priority_id', $newPriorityId)->first();
                $newDueAt = null;
                if ($slaRule && $slaRule->resolution_time_hours) {
                    $newDueAt = $ticket->created_at->addHours($slaRule->resolution_time_hours);
                }
                $ticket->update([
                    'priority_id' => $newPriorityId,
                    'due_at' => $newDueAt,
                ]);
                $ticket->load('priority');
            })
            ->successNotificationTitle('Priority updated successfully!');
    }
}