<?php

namespace App\Filament\Resources\Priorities;

use App\Filament\Resources\Priorities\Pages\CreatePriority;
use App\Filament\Resources\Priorities\Pages\EditPriority;
use App\Filament\Resources\Priorities\Pages\ListPriorities;
use App\Filament\Resources\Priorities\Pages\ViewPriority;
use App\Filament\Resources\Priorities\Schemas\PriorityForm;
use App\Filament\Resources\Priorities\Schemas\PriorityInfolist;
use App\Filament\Resources\Priorities\Tables\PrioritiesTable;
use App\Models\Priority;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PriorityResource extends Resource
{
    protected static ?string $model = Priority::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return PriorityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PriorityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrioritiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPriorities::route('/'),
            'create' => CreatePriority::route('/create'),
            'view' => ViewPriority::route('/{record}'),
            'edit' => EditPriority::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
