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
        $finishedStatuses = [TicketStatusService::STATUS_RESOLVED, TicketStatusService::STATUS_CLOSED];
        
        if ($user->hasRole('administrator')) {
            return [
                Stat::make('Total Tickets', Ticket::count())
                    ->description('All time tickets in system')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->chart([7, 2, 10, 3, 15, 4, 17])
                    ->icon('heroicon-o-ticket')
                    ->color('primary'),

                Stat::make('Unassigned Tickets', Ticket::whereNull('assigned_agent_id')->count())
                    ->description('Needs immediate assignment')
                    ->descriptionIcon('heroicon-m-exclamation-circle')
                    ->chart([1, 3, 2, 5, 4, 2, 6])
                    ->icon('heroicon-o-user-minus')
                    ->color('warning'),

                Stat::make('System Overdue SLA', Ticket::where('due_at', '<', now())
                    ->whereNotIn('status', $finishedStatuses)->count())
                    ->description('Tickets on fire 🔥')
                    ->descriptionIcon('heroicon-m-fire')
                    ->chart([0, 0, 2, 1, 4, 3, 5])
                    ->icon('heroicon-o-bell-alert')
                    ->color('danger'),
            ];
        }


        if ($user->hasRole('supervisor')) {
            return [
                Stat::make('Team Tickets', Ticket::whereNotNull('assigned_agent_id')->count())
                    ->description('Total assigned tickets')
                    ->descriptionIcon('heroicon-m-users')
                    ->chart([3, 5, 4, 8, 7, 10, 9])
                    ->icon('heroicon-o-user-group')
                    ->color('primary'),

                Stat::make('Escalated Tickets', Ticket::where('status', TicketStatusService::STATUS_ESCALATED)->count())
                    ->description('Needs supervisor attention')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->chart([0, 1, 0, 2, 1, 3, 2])
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('warning'),
            ];
        }


        if ($user->hasRole('agent')) {
            return [
                Stat::make('My Assigned Tickets', Ticket::where('assigned_agent_id', $user->id)
                    ->whereNotIn('status', $finishedStatuses)->count())
                    ->description('Active tickets on your plate')
                    ->descriptionIcon('heroicon-m-briefcase')
                    ->chart([2, 3, 3, 5, 4, 6, 5])
                    ->icon('heroicon-o-inbox-stack')
                    ->color('success'),

                Stat::make('My Overdue', Ticket::where('assigned_agent_id', $user->id)
                    ->where('due_at', '<', now())
                    ->whereNotIn('status', $finishedStatuses)->count())
                    ->description('You need to rush these')
                    ->descriptionIcon('heroicon-m-clock')
                    ->chart([0, 0, 1, 0, 2, 1, 3])
                    ->icon('heroicon-o-exclamation-circle')
                    ->color('danger'),
            ];
        }


        return [
            Stat::make('My Tickets', Ticket::where('created_by', $user->id)->count())
                ->description('Total tickets you created')
                ->descriptionIcon('heroicon-m-folder')
                ->chart([1, 0, 2, 1, 3, 2, 4])
                ->icon('heroicon-o-ticket'),

            Stat::make('Open Tickets', Ticket::where('created_by', $user->id)
                ->whereNotIn('status', $finishedStatuses)->count())
                ->description('Waiting for support')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->chart([1, 1, 2, 1, 2, 1, 3])
                ->icon('heroicon-o-envelope-open')
                ->color('warning'),
                
            Stat::make('Resolved', Ticket::where('created_by', $user->id)
                ->whereIn('status', $finishedStatuses)->count())
                ->description('Issues successfully closed')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart([0, 1, 1, 2, 3, 4, 5])
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
