<?php

namespace App\Filament\Resources\Priorities\Schemas;

use App\Models\Priority;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PriorityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('name'),
                TextEntry::make('color'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Priority $record): bool => $record->trashed()),
            ]);
    }
}
