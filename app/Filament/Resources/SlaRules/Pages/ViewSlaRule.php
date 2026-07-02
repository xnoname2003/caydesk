<?php

namespace App\Filament\Resources\SlaRules\Pages;

use App\Filament\Resources\SlaRules\SlaRuleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSlaRule extends ViewRecord
{
    protected static string $resource = SlaRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
