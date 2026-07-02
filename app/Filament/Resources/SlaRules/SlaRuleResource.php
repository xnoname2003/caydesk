<?php

namespace App\Filament\Resources\SlaRules;

use App\Filament\Resources\SlaRules\Pages\CreateSlaRule;
use App\Filament\Resources\SlaRules\Pages\EditSlaRule;
use App\Filament\Resources\SlaRules\Pages\ListSlaRules;
use App\Filament\Resources\SlaRules\Pages\ViewSlaRule;
use App\Filament\Resources\SlaRules\Schemas\SlaRuleForm;
use App\Filament\Resources\SlaRules\Schemas\SlaRuleInfolist;
use App\Filament\Resources\SlaRules\Tables\SlaRulesTable;
use App\Models\SlaRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SlaRuleResource extends Resource
{
    protected static ?string $model = SlaRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return SlaRuleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SlaRuleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SlaRulesTable::configure($table);
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
            'index' => ListSlaRules::route('/'),
            'create' => CreateSlaRule::route('/create'),
            'view' => ViewSlaRule::route('/{record}'),
            'edit' => EditSlaRule::route('/{record}/edit'),
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
