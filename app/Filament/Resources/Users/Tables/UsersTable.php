<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('roles.name')
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
                    ->searchable()
                    ->placeholder('No role assigned'),
                TextColumn::make('team.name')
                    ->label('Team')
                    ->badge() 
                    ->color('cyan')
                    ->searchable()
                    ->placeholder('No team assigned'),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not verified')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not created')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not updated')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
