<?php

namespace App\Filament\Pages\Tickets\Schemas;

use App\Models\Ticket;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use App\Services\TicketStatusService;

class TicketTableSchema
{
    public static function columns(): array
    {
        return [
            TextColumn::make('ticket_number')
                ->label('Ticket')
                ->searchable()
                ->sortable()
                ->weight('medium')
                ->description(fn(Ticket $record): string => "{$record->title} • " . ($record->category->name ?? 'General'))
                ->wrap(),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    TicketStatusService::STATUS_OPEN => 'gray',
                    TicketStatusService::STATUS_ASSIGNED => 'warning',
                    TicketStatusService::STATUS_IN_PROGRESS => 'info',
                    TicketStatusService::STATUS_WAITING_FOR_CUSTOMER => 'warning',
                    TicketStatusService::STATUS_RESOLVED => 'success',
                    TicketStatusService::STATUS_CLOSED => 'gray',
                    TicketStatusService::STATUS_REOPENED => 'danger',
                    TicketStatusService::STATUS_ESCALATED => 'danger',
                    default => 'gray',
                })
                ->icon(fn(string $state): string => match ($state) {
                    TicketStatusService::STATUS_OPEN => 'heroicon-m-inbox',
                    TicketStatusService::STATUS_IN_PROGRESS => 'heroicon-m-arrow-path',
                    TicketStatusService::STATUS_RESOLVED => 'heroicon-m-check-circle',
                    TicketStatusService::STATUS_CLOSED => 'heroicon-m-lock-closed',
                    default => 'heroicon-m-ticket',
                }),

            TextColumn::make('priority.name')
                ->label('Priority')
                ->badge()
                ->color(fn($state): string => match ($state) {
                    'High', 'Critical' => 'danger',
                    'Medium' => 'warning',
                    'Low' => 'gray',
                    default => 'primary',
                })
                ->sortable(),

            TextColumn::make('assignedAgent.name')
                ->label('Assignee')
                ->default('Unassigned')
                ->icon(fn($state) => $state === 'Unassigned' ? 'heroicon-m-user-minus' : 'heroicon-m-user-circle')
                ->iconColor(fn($state) => $state === 'Unassigned' ? 'gray' : 'primary')
                ->color(fn($state) => $state === 'Unassigned' ? 'gray' : 'primary')
                ->searchable()
                ->toggleable(),

            TextColumn::make('updated_at')
                ->label('Last Update')
                ->since()
                ->sortable()
                ->color('gray'),
        ];
    }

    public static function filters(): array
    {
        return [
            SelectFilter::make('status')
                ->options([
                    TicketStatusService::STATUS_OPEN => TicketStatusService::STATUS_OPEN,
                    TicketStatusService::STATUS_ASSIGNED => TicketStatusService::STATUS_ASSIGNED,
                    TicketStatusService::STATUS_IN_PROGRESS => TicketStatusService::STATUS_IN_PROGRESS,
                    TicketStatusService::STATUS_WAITING_FOR_CUSTOMER => TicketStatusService::STATUS_WAITING_FOR_CUSTOMER,
                    TicketStatusService::STATUS_RESOLVED => TicketStatusService::STATUS_RESOLVED,
                    TicketStatusService::STATUS_CLOSED => TicketStatusService::STATUS_CLOSED,
                    TicketStatusService::STATUS_REOPENED => TicketStatusService::STATUS_REOPENED,
                    TicketStatusService::STATUS_ESCALATED => TicketStatusService::STATUS_ESCALATED,
                ])
                ->multiple(),

            SelectFilter::make('priority_id')
                ->relationship('priority', 'name')
                ->label('Priority'),

            SelectFilter::make('category_id')
                ->relationship('category', 'name')
                ->label('Category'),

            Filter::make('overdue')
                ->label('Overdue Tickets')
                ->query(fn(Builder $query): Builder => $query->where('due_at', '<', now())->whereNotIn('status', [TicketStatusService::STATUS_RESOLVED, TicketStatusService::STATUS_CLOSED]))
                ->toggle(),
        ];
    }

    public static function actions(): array
    {
        return [
            Action::make('view')
                ->label('View')
                ->icon('heroicon-m-eye')
                ->url(fn(Ticket $record): string => url('/app/tickets/' . $record->ticket_number))
        ];
    }
}