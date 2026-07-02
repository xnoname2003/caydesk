<?php

namespace App\Filament\Resources\Priorities\Pages;

use App\Filament\Resources\Priorities\PriorityResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPriority extends ViewRecord
{
    protected static string $resource = PriorityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
