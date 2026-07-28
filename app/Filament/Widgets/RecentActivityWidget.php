<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use App\Models\Comment;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected ?string $pollingInterval = '15s';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();
        
        $query = Activity::query()->latest();

        if (! $user->hasRole('administrator')) {
            $query->where(function ($q) use ($user) {
                
                $q->where('causer_id', $user->id);

                if ($user->hasRole('supervisor')) {
                    $q->orWhere(function ($subQ) use ($user) {
                        $subQ->where('subject_type', Team::class)
                             ->where('subject_id', $user->team_id);
                    })
                    ->orWhere(function ($subQ) use ($user) {
                        $subQ->where('subject_type', User::class)
                             ->whereIn('subject_id', User::where('team_id', $user->team_id)->pluck('id'));
                    })
                    ->orWhereHasMorph('subject', [Ticket::class], function (Builder $ticketQuery) use ($user) {
                        $ticketQuery->whereHas('assignedAgent', function ($agentQuery) use ($user) {
                            $agentQuery->where('team_id', $user->team_id);
                        });
                    })
                    ->orWhereHasMorph('subject', [Comment::class], function (Builder $commentQuery) use ($user) {
                        $commentQuery->whereHas('ticket.assignedAgent', function ($agentQuery) use ($user) {
                            $agentQuery->where('team_id', $user->team_id);
                        });
                    });
                    
                } elseif ($user->hasRole('agent')) {
                    $q->orWhere(function ($subQ) use ($user) {
                        $subQ->where('subject_type', User::class)
                             ->where('subject_id', $user->id);
                    })
                    ->orWhereHasMorph('subject', [Ticket::class], function (Builder $ticketQuery) use ($user) {
                        $ticketQuery->where('assigned_agent_id', $user->id)
                                    ->orWhere('created_by', $user->id);
                    })
                    ->orWhereHasMorph('subject', [Comment::class], function (Builder $commentQuery) use ($user) {
                        $commentQuery->whereHas('ticket', function ($ticketQuery) use ($user) {
                            $ticketQuery->where('assigned_agent_id', $user->id)
                                        ->orWhere('created_by', $user->id);
                        });
                    });
                    
                } else {
                    $q->orWhere(function ($subQ) use ($user) {
                        $subQ->where('subject_type', User::class)
                             ->where('subject_id', $user->id);
                    })
                    ->orWhereHasMorph('subject', [Ticket::class], function (Builder $ticketQuery) use ($user) {
                        $ticketQuery->where('created_by', $user->id);
                    })
                    ->orWhereHasMorph('subject', [Comment::class], function (Builder $commentQuery) use ($user) {
                        $commentQuery->whereHas('ticket', function ($ticketQuery) use ($user) {
                            $ticketQuery->where('created_by', $user->id);
                        });
                    });
                }
            });
        }

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