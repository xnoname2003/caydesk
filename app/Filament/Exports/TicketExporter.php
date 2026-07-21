<?php

namespace App\Filament\Exports;

use App\Models\Ticket;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TicketExporter extends Exporter
{
    protected static ?string $model = Ticket::class;

    public function getFileName(Export $export): string
    {
        return 'Report_Tickets_'.now()->format('Y_m_d_His');
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('ticket_number')->label('Ticket Number'),
            ExportColumn::make('title')->label('Title'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('priority.name')->label('Priority'),
            ExportColumn::make('category.name')->label('Category'),
            ExportColumn::make('assignedAgent.name')->label('Assigned Agent'),
            ExportColumn::make('creator.name')->label('Customer'),
            ExportColumn::make('created_at')->label('Created Date'),
            ExportColumn::make('due_at')->label('Due Date'),
            ExportColumn::make('resolved_at')->label('Resolved Date'),
            ExportColumn::make('closed_at')->label('Closed Date'),
            ExportColumn::make('sla_status')
                ->label('SLA Status')
                ->state(function (Ticket $record): string {
                    if (! $record->due_at) {
                        return '-';
                    }
                    if ($record->resolved_at) {
                        return $record->resolved_at > $record->due_at ? 'OVERDUE' : 'On Time';
                    }

                    return now() > $record->due_at ? 'OVERDUE' : 'On Track';
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your ticket export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
