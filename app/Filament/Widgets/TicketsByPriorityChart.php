<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use App\Services\ColorService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TicketsByPriorityChart extends ChartWidget
{
    protected ?string $heading = 'Tickets by Priority';

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = '15s';

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['administrator']);
    }

    protected function getData(): array
    {
        $user = auth()->user();
        
        $query = Ticket::query()->with('priority');

        if ($user->hasRole('agent')) {
            $query->where('assigned_agent_id', $user->id);
        }

        $ticketCounts = $query->select('priority_id', DB::raw('count(*) as total'))
            ->groupBy('priority_id')
            ->get();

        $data = [];
        $backgroundColors = [];

        foreach ($ticketCounts as $item) {
            $name = $item->priority ? $item->priority->name : 'Unknown';
            $data[$name] = $item->total;

            $dbColor = $item->priority ? $item->priority->color : null;
            $backgroundColors[] = ColorService::getHex($dbColor);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Tickets',
                    'data' => array_values($data),
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}