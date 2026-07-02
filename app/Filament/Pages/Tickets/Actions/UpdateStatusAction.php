<?php

namespace App\Filament\Pages\Tickets\Actions;

use App\Models\Ticket;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use App\Services\TicketStatusService;

class UpdateStatusAction
{
    public static function make(Ticket $ticket): Action
    {
        return Action::make('updateStatus')
            ->label('Update Status')
            ->button()
            ->outlined()
            ->icon('heroicon-m-arrow-path')
            ->visible(function () use ($ticket) {
                $user = auth()->user();
                if ($user->hasRole('customer') || $user->hasRole('Customer'))
                    return false;

                $statusService = app(TicketStatusService::class);
                $options = $statusService->getAllowedNextStatuses($ticket->status);

                $isAgent = $user->hasRole('agent') || $user->hasRole('Agent');

                if ($isAgent) {
                    if ($ticket->assigned_agent_id !== $user->id)
                        return false;

                    $agentAllowed = [
                        TicketStatusService::STATUS_IN_PROGRESS,
                        TicketStatusService::STATUS_WAITING_FOR_CUSTOMER,
                        TicketStatusService::STATUS_RESOLVED,
                        TicketStatusService::STATUS_ESCALATED,
                    ];

                    $validForAgent = array_intersect(array_values($options), $agentAllowed);
                    return count($validForAgent) > 0;
                }

                return count($options) > 0;
            })
            ->form([
                Select::make('status')
                    ->label('Update Status')
                    ->options(function () use ($ticket) {
                        $statusService = app(TicketStatusService::class);
                        $options = $statusService->getAllowedNextStatuses($ticket->status);

                        if (auth()->user()->hasRole('agent')) {
                            $agentAllowed = [
                                TicketStatusService::STATUS_IN_PROGRESS,
                                TicketStatusService::STATUS_WAITING_FOR_CUSTOMER,
                                TicketStatusService::STATUS_RESOLVED,
                                TicketStatusService::STATUS_ESCALATED,
                            ];

                            foreach ($options as $key => $value) {
                                if (!in_array($key, $agentAllowed)) {
                                    unset($options[$key]);
                                }
                            }
                        }

                        return $options;
                    })
                    ->required()
                    ->default($ticket->status)
            ])
            ->action(function (array $data) use ($ticket) {
                $ticket->update(['status' => $data['status']]);
            })
            ->successNotificationTitle('Status updated successfully!');
    }
}