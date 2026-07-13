<?php

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Resources\Activities\ActivityResource;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Activities\Widgets\ActivityStats;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ActivityStats::class,
        ];
    }
}
