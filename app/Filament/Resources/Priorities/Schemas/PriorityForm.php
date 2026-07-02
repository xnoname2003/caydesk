<?php

namespace App\Filament\Resources\Priorities\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Str;

class PriorityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('color')
                    ->options(function () {
                        $colors = array_keys(FilamentColor::getColors());
                        return collect($colors)->mapWithKeys(function ($color) {
                            return [$color => Str::title($color)];
                        })->toArray();
                    })
                    ->required()
                    ->default('primary')
                    ->live(),
                Placeholder::make('preview')
                    ->label('Label Preview')
                    ->content(function (callable $get) {
                        $name = $get('name') ?: 'Sample Label';
                        $color = $get('color') ?: 'primary';
                        return new HtmlString(
                            Blade::render('<x-filament::badge color="' . $color . '">' . $name . '</x-filament::badge>')
                        );
                    }),
            ]);
    }
}
