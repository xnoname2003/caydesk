<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use App\Services\TicketStatusService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $user = auth()->user();
        $finishedStatuses = [TicketStatusService::STATUS_RESOLVED, TicketStatusService::STATUS_CLOSED];
        
        if ($user->hasRole('administrator')) {
            $resolvedTickets = Ticket::whereNotNull('resolved_at')->get();
            $avgHours = $resolvedTickets->count() > 0 
                ? $resolvedTickets->avg(fn($t) => $t->created_at->diffInHours($t->resolved_at))
                : 0;
            $avgResolutionText = $avgHours > 0 ? number_format($avgHours, 1) . ' Hours' : 'N/A';

            return [
                Stat::make('Total Tickets', Ticket::count())
                    ->description('All time tickets in system')
                    ->icon('heroicon-o-ticket')
                    ->color('primary'),

                Stat::make('Unassigned Tickets', Ticket::whereNull('assigned_agent_id')->count())
                    ->description('Needs immediate assignment')
                    ->icon('heroicon-o-user-minus')
                    ->color('warning'),

                Stat::make('System Overdue SLA', Ticket::where('due_at', '<', now())
                    ->whereNotIn('status', $finishedStatuses)->count())
                    ->description('Tickets on fire 🔥')
                    ->icon('heroicon-o-bell-alert')
                    ->color('danger'),

                Stat::make('Tickets This Week', Ticket::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count())
                    ->description('New tickets created this week')
                    ->icon('heroicon-o-calendar')
                    ->color('success'),

                Stat::make('Avg Resolution Time', $avgResolutionText)
                    ->description('Average time to resolve tickets')
                    ->icon('heroicon-o-clock')
                    ->color('info'),
            ];
        }

        if ($user->hasRole('supervisor')) {
            return [
                Stat::make('Team Tickets', Ticket::whereNotNull('assigned_agent_id')->count())
                    ->description('Total assigned tickets')
                    ->icon('heroicon-o-user-group')
                    ->color('primary'),

                Stat::make('Escalated Tickets', Ticket::where('status', TicketStatusService::STATUS_ESCALATED)->count())
                    ->description('Needs supervisor attention')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('warning'),

                Stat::make('Open Tickets', Ticket::where('status', TicketStatusService::STATUS_OPEN)->count())
                    ->description('Tickets waiting to be processed')
                    ->icon('heroicon-o-envelope-open')
                    ->color('info'),

                Stat::make('Overdue Tickets', Ticket::where('due_at', '<', now())
                    ->whereNotIn('status', $finishedStatuses)->count())
                    ->description('Team tickets past SLA')
                    ->icon('heroicon-o-clock')
                    ->color('danger'),
            ];
        }

        if ($user->hasRole('agent')) {
            return [
                Stat::make('My Assigned Tickets', Ticket::where('assigned_agent_id', $user->id)
                    ->whereNotIn('status', $finishedStatuses)->count())
                    ->description('Active tickets on your plate')
                    ->icon('heroicon-o-inbox-stack')
                    ->color('success'),

                Stat::make('My Overdue', Ticket::where('assigned_agent_id', $user->id)
                    ->where('due_at', '<', now())
                    ->whereNotIn('status', $finishedStatuses)->count())
                    ->description('You need to rush these')
                    ->icon('heroicon-o-exclamation-circle')
                    ->color('danger'),
                    
                Stat::make('My Open Tickets', Ticket::where('assigned_agent_id', $user->id)
                    ->where('status', TicketStatusService::STATUS_OPEN)->count())
                    ->description('Assigned but not started')
                    ->icon('heroicon-o-envelope-open')
                    ->color('warning'),
            ];
        }

        return [
            Stat::make('My Tickets', Ticket::where('created_by', $user->id)->count())
                ->description('Total tickets you created')
                ->icon('heroicon-o-folder')
                ->color('primary'),

            Stat::make('Open Tickets', Ticket::where('created_by', $user->id)
                ->whereNotIn('status', $finishedStatuses)->count())
                ->description('Waiting for support')
                ->icon('heroicon-o-arrow-path')
                ->color('warning'),
                
            Stat::make('Resolved', Ticket::where('created_by', $user->id)
                ->whereIn('status', $finishedStatuses)->count())
                ->description('Issues successfully closed')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}