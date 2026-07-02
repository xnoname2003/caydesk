<?php

namespace App\Filament\Resources\Labels;

use App\Filament\Resources\Labels\Pages\CreateLabel;
use App\Filament\Resources\Labels\Pages\EditLabel;
use App\Filament\Resources\Labels\Pages\ListLabels;
use App\Filament\Resources\Labels\Pages\ViewLabel;
use App\Filament\Resources\Labels\Schemas\LabelForm;
use App\Filament\Resources\Labels\Schemas\LabelInfolist;
use App\Filament\Resources\Labels\Tables\LabelsTable;
use App\Models\Label;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LabelResource extends Resource
{
    protected static ?string $model = Label::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return LabelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LabelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LabelsTable::configure($table);
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
            'index' => ListLabels::route('/'),
            'create' => CreateLabel::route('/create'),
            'view' => ViewLabel::route('/{record}'),
            'edit' => EditLabel::route('/{record}/edit'),
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
