<?php

namespace App\Filament\Resources\SlaRules\Pages;

use App\Filament\Resources\SlaRules\SlaRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSlaRules extends ListRecords
{
    protected static string $resource = SlaRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
