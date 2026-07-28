<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use App\Services\ColorService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TicketsByCategoryChart extends ChartWidget
{
    protected ?string $heading = 'Tickets by Category';

    protected static ?int $sort = 3;

    protected ?string $pollingInterval = '15s';

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['administrator']);
    }

    protected function getData(): array
    {
        $user = auth()->user();
        
        $query = Ticket::query()->with('category');

        if ($user->hasRole('agent')) {
            $query->where('assigned_agent_id', $user->id);
        }

        $ticketCounts = $query->select('category_id', DB::raw('count(*) as total'))
            ->groupBy('category_id')
            ->get();

        $data = [];
        foreach ($ticketCounts as $item) {
            $name = $item->category ? $item->category->name : 'Unknown';
            $data[$name] = $item->total;
        }

        $palette = ['primary', 'success', 'warning', 'danger', 'violet', 'info', 'orange'];
        $backgroundColors = [];
        $i = 0;
        foreach ($data as $key => $value) {
            $backgroundColors[] = ColorService::getHex($palette[$i % count($palette)]);
            $i++;
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