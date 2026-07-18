<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (callable $set, $state) {
                        if (empty($state)) {
                            $set('team_id', null);

                            return;
                        }

                        $roleId = is_array($state) ? ($state[0] ?? null) : $state;

                        if ($roleId) {
                            $role = Role::find($roleId);
                            if ($role && ! in_array(strtolower($role->name), ['supervisor', 'agent'])) {
                                $set('team_id', null);
                            }
                        }
                    }),
                Select::make('team_id')
                    ->relationship('team', 'name')
                    ->preload()
                    ->dehydrated(true)
                    ->required(function (callable $get) {
                        $roleId = is_array($get('roles')) ? ($get('roles')[0] ?? null) : $get('roles');
                        $role = $roleId ? Role::find($roleId) : null;

                        return $role ? in_array(strtolower($role->name), ['supervisor', 'agent']) : false;
                    })
                    ->visible(function (callable $get) {
                        $roleId = is_array($get('roles')) ? ($get('roles')[0] ?? null) : $get('roles');
                        $role = $roleId ? Role::find($roleId) : null;

                        return $role ? in_array(strtolower($role->name), ['supervisor', 'agent']) : false;
                    }),
            ]);
    }
}
