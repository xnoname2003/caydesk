<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Services\ColorService;

class AgentAvgResolutionTimeChart extends ChartWidget
{
    protected ?string $heading = 'Average Resolution Time (Hours)';

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = '15s';

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['supervisor']);
    }

    protected function getData(): array
    {
        $user = auth()->user();

        $query = Ticket::query()
            ->with('assignedAgent')
            ->whereNotNull('assigned_agent_id')
            ->whereNotNull('resolved_at');

        $query->whereHas('assignedAgent', function ($q) use ($user) {
            $q->where('team_id', $user->team_id);
        });

        $resolutionData = $query->select(
            'assigned_agent_id',
            DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_time')
        )
            ->groupBy('assigned_agent_id')
            ->get();

        $labels = [];
        $data = [];

        foreach ($resolutionData as $item) {
            $labels[] = $item->assignedAgent ? $item->assignedAgent->name : 'Unknown Agent';
            $data[] = round($item->avg_time, 1);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Avg Hours to Resolve',
                    'data' => $data,
                    'backgroundColor' => ColorService::getHex('emerald'),
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