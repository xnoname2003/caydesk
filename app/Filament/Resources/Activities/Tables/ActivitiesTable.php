<?php

namespace App\Filament\Resources\Activities\Tables;

use App\Filament\Pages\ActivityLog;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject_target')
                    ->label('TARGET #')
                    ->state(function ($record) {
                        // 1. Handle Ticket
                        if ($record->subject_type === Ticket::class) {
                            $ticket = $record->subject ?? Ticket::withTrashed()->find($record->subject_id);

                            return $ticket?->ticket_number ?? 'UNKNOWN';
                        }

                        // 2. Handle Comment (Tarik nomor tiket induknya)
                        if ($record->subject_type === Comment::class) {
                            $comment = $record->subject ?? Comment::withTrashed()->find($record->subject_id);
                            $ticket = $comment ? Ticket::withTrashed()->find($comment->ticket_id) : null;

                            return $ticket?->ticket_number ?? 'UNKNOWN';
                        }

                        // 👇 3. Handle Master Data (Category, Label, User, Priority, dll) 👇

                        // Kalau data targetnya masih ada di database
                        if ($record->subject) {
                            // Kita tarik kolom 'name'-nya. Kalau kebetulan tabelnya gak punya kolom name,
                            // dia tetep punya fallback aman ke 'SYSTEM'
                            return strtoupper($record->subject->name ?? 'SYSTEM');
                        }

                        // Kalau data master-nya udah dihapus permanen (tanpa soft delete)
                        // Outputnya bakal jadi "DELETED CATEGORY" atau "DELETED USER"
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
            ->filters([])
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
