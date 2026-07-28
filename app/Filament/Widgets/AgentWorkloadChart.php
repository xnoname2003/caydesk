<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Services\ColorService;

class AgentWorkloadChart extends ChartWidget
{
    protected ?string $heading = 'Agent Workload (Active Tickets)';

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = '15s';

    public static function canView(): bool
    {
        // Hanya bisa dilihat oleh Supervisor dan Administrator
        return auth()->user()->hasAnyRole(['supervisor']);
    }

    protected function getData(): array
    {
        $user = auth()->user();

        $query = Ticket::query()
            ->with('assignedAgent')
            ->whereNotNull('assigned_agent_id')
            ->whereNotIn('status', ['resolved', 'closed']);

        $query->whereHas('assignedAgent', function ($q) use ($user) {
            $q->where('team_id', $user->team_id);
        });

        $workload = $query->select('assigned_agent_id', DB::raw('count(*) as total'))
            ->groupBy('assigned_agent_id')
            ->get();

        $labels = [];
        $data = [];

        foreach ($workload as $item) {
            $labels[] = $item->assignedAgent ? $item->assignedAgent->name : 'Unknown Agent';
            $data[] = $item->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Active Tickets',
                    'data' => $data,
                    'backgroundColor' => ColorService::getHex('info'),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}