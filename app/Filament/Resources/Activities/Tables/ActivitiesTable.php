<?php

namespace App\Filament\Resources\Activities\Tables;

use App\Models\Comment;
use App\Models\Ticket;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject_target')
                    ->label('TARGET #')
                    ->state(function ($record) {
                        if ($record->subject_type === \App\Models\Ticket::class) {
                            $ticket = $record->subject ?? \App\Models\Ticket::withTrashed()->find($record->subject_id);
                            return $ticket?->ticket_number ?? 'UNKNOWN';
                        }
                        
                        if ($record->subject_type === \App\Models\Comment::class) {
                            $comment = $record->subject ?? \App\Models\Comment::withTrashed()->find($record->subject_id);
                            $ticket = $comment ? \App\Models\Ticket::withTrashed()->find($comment->ticket_id) : null;
                            return $ticket?->ticket_number ?? 'UNKNOWN';
                        }
                        
                        return 'SYSTEM';
                    })
                    ->color('danger')
                    ->searchable(),

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
                    ->searchable(),

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
            ->bulkActions([]);
    }
}
