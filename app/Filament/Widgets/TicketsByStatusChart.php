<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Support\Colors\Color;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TicketsByStatusChart extends ChartWidget
{
    protected ?string $heading = 'Tickets by Status';

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = '15s';

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['administrator', 'agent']);
    }

    protected function getData(): array
    {
        $user = auth()->user();
        $query = Ticket::query();

        if ($user->hasRole('agent')) {
            $query->where('assigned_agent_id', $user->id);
        }

        $data = $query->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Tembak langsung pakai standar Hex Tailwind/Filament. DIJAMIN NYALA.
        $backgroundColors = collect(array_keys($data))->map(function ($status) {
            return match (strtolower($status)) {
                'open', 'waiting for customer' => '#f59e0b', // Amber 500
                'assigned', 'in progress' => '#3b82f6', // Blue 500
                'resolved', 'closed' => '#10b981', // Emerald 500
                'escalated' => '#ef4444', // Red 500
                'reopened' => '#f97316', // Orange 500
                default => '#71717a', // Zinc 500 (Gray)
            };
        })->toArray();

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
