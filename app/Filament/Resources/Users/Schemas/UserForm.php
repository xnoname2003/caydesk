<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->dehydrated(fn(?string $state): bool => filled($state)),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->required()
                    ->live(),
                Select::make('team_id')
                    ->relationship('team', 'name')
                    ->preload()
                    ->required()
                    ->visible(function (callable $get) {
                        $roleId = $get('roles');
                        if (empty($roleId)) {
                            return false;
                        }
                        $role = Role::find($roleId);
                        if ($role) {
                            return in_array(strtolower($role->name), ['supervisor', 'agent']);
                        }
                        return false;
                    }),
            ]);
    }
}
