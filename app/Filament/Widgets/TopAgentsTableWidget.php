<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use App\Services\TicketStatusService;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;

class TopAgentsTableWidget extends BaseWidget
{
    protected static ?int $sort = 2; // Taruh di baris kedua
    protected ?string $pollingInterval = '15s';
    protected int | string | array $columnSpan = 1; // Biar ukurannya setengah layar

    // 👇 INI KUNCI ROLE-BASED NYA 👇
    public static function canView(): bool
    {
        return auth()->user()->hasRole('administrator');
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->role('agent') 
                    // 👇 UBAH KATA 'tickets' JADI 'assignedTickets' DI SINI 👇
                    ->withCount(['assignedTickets as resolved_count' => function (Builder $query) {
                        $query->where('status', TicketStatusService::STATUS_RESOLVED);
                    }])
                    ->orderByDesc('resolved_count')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('NAMA AGENT')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('resolved_count')
                    ->label('TIKET RESOLVED')
                    ->badge()
                    ->color('success'),
            ])
            ->heading('Top 5 Agents')
            ->description('Agents with the most resolved tickets.')
            ->paginated(false);
    }
}
