<?php

namespace App\Filament\Resources\SlaRules\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class SlaRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('priority_id')
                    ->relationship('priority', 'name')
                    ->preload()
                    ->required(),
                TextInput::make('response_time_hours')
                    ->required()
                    ->numeric(),
                TextInput::make('resolution_time_hours')
                    ->required()
                    ->numeric(),
            ]);
    }
}
