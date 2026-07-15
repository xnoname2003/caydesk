<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use App\Services\TicketStatusService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $user = auth()->user();

        // Array status yang dianggap "selesai" biar kodenya makin DRY
        $finishedStatuses = [TicketStatusService::STATUS_RESOLVED, TicketStatusService::STATUS_CLOSED];

        if ($user->hasRole('administrator')) {
            return [
                Stat::make('Total Tickets', Ticket::count())
                    ->description('All time tickets in system')
                    ->descriptionIcon('heroicon-m-ticket')
                    ->color('primary'),

                Stat::make('Unassigned Tickets', Ticket::whereNull('assigned_agent_id')->count())
                    ->description('Needs immediate assignment')
                    ->descriptionIcon('heroicon-m-user-minus')
                    ->color('warning'),

                Stat::make('System Overdue SLA', Ticket::where('due_at', '<', now())
                    ->whereNotIn('status', $finishedStatuses)->count())
                    ->description('Tickets on fire 🔥')
                    ->descriptionIcon('heroicon-m-fire')
                    ->color('danger'),
            ];
        }

        if ($user->hasRole('supervisor')) {
            return [
                Stat::make('Team Tickets', Ticket::whereNotNull('assigned_agent_id')->count())
                    ->description('Total assigned tickets')
                    ->color('primary'),

                Stat::make('Escalated Tickets', Ticket::where('status', TicketStatusService::STATUS_ESCALATED)->count())
                    ->description('Needs supervisor attention')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('warning'),
            ];
        }

        if ($user->hasRole('agent')) {
            return [
                Stat::make('My Assigned Tickets', Ticket::where('assigned_agent_id', $user->id)
                    ->whereNotIn('status', $finishedStatuses)->count())
                    ->description('Active tickets on your plate')
                    ->color('success'),

                Stat::make('My Overdue', Ticket::where('assigned_agent_id', $user->id)
                    ->where('due_at', '<', now())
                    ->whereNotIn('status', $finishedStatuses)->count())
                    ->description('You need to rush these')
                    ->color('danger'),
            ];
        }

        return [
            Stat::make('My Tickets', Ticket::where('created_by', $user->id)->count())
                ->description('Total tickets you created'),

            Stat::make('Open Tickets', Ticket::where('created_by', $user->id)
                ->whereNotIn('status', $finishedStatuses)->count())
                ->description('Waiting for support'),

            Stat::make('Resolved', Ticket::where('created_by', $user->id)
                ->whereIn('status', $finishedStatuses)->count())
                ->color('success'),
        ];
    }
}
