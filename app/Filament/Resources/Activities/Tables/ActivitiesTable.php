<?php

namespace App\Filament\Resources\Activities\Tables;

use App\Filament\Pages\ActivityLog;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\Label;
use App\Models\Priority;
use App\Models\Role;
use App\Models\SlaRule;
use App\Models\Team;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject_target')
                    ->label('TARGET #')
                    ->state(function ($record) {
                        if ($record->subject_type === Ticket::class) {
                            $ticket = $record->subject ?? Ticket::withTrashed()->find($record->subject_id);

                            return $ticket?->ticket_number ?? 'UNKNOWN';
                        }

                        if ($record->subject_type === Comment::class) {
                            $comment = $record->subject ?? Comment::withTrashed()->find($record->subject_id);
                            $ticket = $comment ? Ticket::withTrashed()->find($comment->ticket_id) : null;

                            return $ticket?->ticket_number ?? 'UNKNOWN';
                        }


                        if ($record->subject) {
                            return strtoupper($record->subject->name ?? 'SYSTEM');
                        }

                        $moduleName = class_basename($record->subject_type);

                        return 'DELETED '.strtoupper($moduleName);
                    })
                    ->color('danger')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->whereHasMorph('subject', [Ticket::class], function ($morphQuery) use ($search) {
                                $morphQuery->withTrashed()->where('ticket_number', 'like', "%{$search}%");
                            })
                                ->orWhereHasMorph('subject', [Comment::class], function ($morphQuery) use ($search) {
                                    $morphQuery->withTrashed()->whereHas('ticket', function ($ticketQuery) use ($search) {
                                        $ticketQuery->withTrashed()->where('ticket_number', 'like', "%{$search}%");
                                    });
                                });
                        });
                    }),
                TextColumn::make('event')
                    ->label('EVENT')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => strtoupper($state ?? 'updated'))
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('subject_type')
                    ->label('MODULE')
                    ->formatStateUsing(fn (string $state): string => strtoupper(class_basename($state))),

                TextColumn::make('description')
                    ->label('ACTION SUMMARY')
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->description(fn ($record): string => 'by '.($record->causer ? $record->causer->name : 'System'))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->where('description', 'like', "%{$search}%")
                                ->orWhereHasMorph('causer', [User::class], function ($userQuery) use ($search) {
                                    $userQuery->where('name', 'like', "%{$search}%");
                                });
                        });
                    }),
                TextColumn::make('created_at')
                    ->label('TIMESTAMP')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->color('gray')
                    ->alignEnd(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('event')
                    ->label('Filter by Event')
                    ->options([
                        'created' => 'CREATED',
                        'updated' => 'UPDATED',
                        'deleted' => 'DELETED',
                    ]),
                
                SelectFilter::make('subject_type')
                    ->label('Filter by Module')
                    ->options([
                        Ticket::class => 'TICKET',
                        Comment::class => 'COMMENT',
                        User::class => 'USER',
                        Category::class => 'CATEGORY',
                        Label::class => 'LABEL',
                        Priority::class => 'PRIORITY',
                        SlaRule::class => 'SLA RULE',
                        Team::class => 'TEAM',
                        Attachment::class => 'ATTACHMENT',
                        Role::class => 'ROLE',
                    ]),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('From Date'),
                        DatePicker::make('created_until')->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([])
            ->bulkActions([])
            ->recordUrl(function ($record) {
                $ticketNumber = null;

                if ($record->subject_type === Ticket::class) {
                    $ticket = $record->subject ?? Ticket::withTrashed()->find($record->subject_id);
                    $ticketNumber = $ticket?->ticket_number;
                } elseif ($record->subject_type === Comment::class) {
                    $comment = $record->subject ?? Comment::withTrashed()->find($record->subject_id);
                    $ticket = $comment ? Ticket::withTrashed()->find($comment->ticket_id) : null;
                    $ticketNumber = $ticket?->ticket_number;
                }
                if ($ticketNumber) {
                    return ActivityLog::getUrl(['ticketNumber' => $ticketNumber]);
                }

                return null;
            });
    }
}
