<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardState extends BaseWidget
{
    protected function getColumns(): int
    {
        return 2;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Speakers', \App\Models\Speaker::count())
                ->description('Total number of speakers')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success')
                ->chart([1, 2, 3, 4, 5, 4, 1, 1]),

            Stat::make('Total Conferences', \App\Models\Conference::count())
                ->description('Total number of conferences')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary')
                ->chart([1, 2, 3, 4, 5, 4, 1, 1]),

//            Total Revenue

            Stat::make('Total Revenue', \App\Models\Attendee::sum('ticket_cost') / 100)
                ->description('Total revenue from ticket sales')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('warning')
                ->chart([1, 2, 3, 4, 5, 4, 1, 1]),


            Stat::make('Total Talks', \App\Models\Talk::count())
                ->description('Total number of talks')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('info')
                ->chart([1, 2, 3, 4, 5, 4, 1, 1]),


        ];
    }
}
