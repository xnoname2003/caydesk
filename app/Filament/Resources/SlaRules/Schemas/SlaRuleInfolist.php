<?php

namespace App\Filament\Resources\SlaRules\Schemas;

use App\Models\SlaRule;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SlaRuleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('name'),
                TextEntry::make('priority.name')
                    ->label('Priority')
                    ->badge()
                    ->color(fn ($record) => $record->priority ? $record->priority->color : 'gray')
                    ->placeholder('-'),
                TextEntry::make('response_time_hours')
                    ->numeric(),
                TextEntry::make('resolution_time_hours')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (SlaRule $record): bool => $record->trashed()),
            ]);
    }
}
