<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color(function ($state) {
                        $role = is_array($state) ? ($state[0] ?? null) : $state;

                        return match ($role) {
                            'administrator' => 'danger',
                            'supervisor' => 'success',
                            'agent' => 'info',
                            'customer' => 'warning',
                            default => 'gray',
                        };
                    })
                    ->placeholder('No role assigned'),
                TextEntry::make('team.name')
                    ->label('Team')
                    ->badge()
                    ->color('primary') 
                    ->placeholder('No team assigned'),
                TextEntry::make('email_verified_at')
                    ->dateTime()
                    ->placeholder('Not verified'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('Not created'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('Not updated'),
            ]);
    }
}
