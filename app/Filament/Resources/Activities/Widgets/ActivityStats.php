<?php

namespace App\Filament\Resources\Activities\Widgets;

use App\Models\Activity;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActivityStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('TRACKED TRANSACTIONS', Activity::count())
                ->description('Total audit logs recorded')
                ->descriptionIcon('heroicon-m-hashtag')
                ->color('danger'),
                
            Stat::make('ORPHAN LOGS', Activity::where('event', 'deleted')->count())
                ->description('Deleted transactions')
                ->descriptionIcon('heroicon-m-trash') // Icon tempat sampah biar sesuai konteks
                ->color('warning'),
                
            Stat::make('USER ACTIVITIES', Activity::whereNotNull('causer_id')->count())
                ->description('Events triggered by users')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
