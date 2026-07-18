<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use App\Models\Ticket;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected ?string $pollingInterval = '15s';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();
        
        // 1. Definisikan query di luar (Jangan pakai closure function lagi)
        $query = Activity::query()->latest();

        // 2. Terapkan filter Role langsung ke variable $query
        if (! $user->hasRole(['administrator', 'supervisor'])) {
            if ($user->hasRole('agent')) {
                $query->where(function ($q) use ($user) {
                    $q->where('causer_id', $user->id)
                        ->orWhereHasMorph('subject', [Ticket::class], function (Builder $ticketQuery) use ($user) {
                            $ticketQuery->where('assigned_agent_id', $user->id)
                                ->orWhere('created_by', $user->id);
                        });
                });
            } else {
                // Customer
                $query->whereHasMorph('subject', [Ticket::class], function (Builder $ticketQuery) use ($user) {
                    $ticketQuery->where('created_by', $user->id);
                });
            }
        }

        // 3. Passing variable $query yang udah matang ke tabel
        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('EVENT')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'created', 'ticket has been created' => 'success',
                        'updated', 'ticket has been updated' => 'warning',
                        'labels have been updated' => 'info',
                        'deleted', 'ticket has been deleted' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label('MODULE')
                    ->formatStateUsing(function ($state) {
                        if (! $state) return '-';
                        $parts = explode('\\', $state);
                        return strtoupper(end($parts));
                    }),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('PERFORMED BY')
                    ->default('System')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('TIMESTAMP')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->heading('Recent Activity')
            ->description('Live updates on tickets and system actions.')
            ->defaultPaginationPageOption(5)
            ->poll('10s');
    }
}
