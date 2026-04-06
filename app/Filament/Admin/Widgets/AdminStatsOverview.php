<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Event;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $now = Carbon::now();

        $totalAdmins = User::query()->where('is_admin', true)->count();
        $totalUsers = User::query()->where('is_admin', false)->count();

        $totalEvents = Event::query()->count();
        $cancelledEvents = Event::query()->where('is_published', false)->count();

        $endedEvents = Event::query()
            ->where('is_published', true)
            ->where(function ($q) use ($now) {
                $q->whereNotNull('end_at')->where('end_at', '<', $now)
                    ->orWhere(function ($qq) use ($now) {
                        $qq->whereNull('end_at')->where('start_at', '<', $now);
                    });
            })
            ->count();

        $activeEvents = Event::query()
            ->where('is_published', true)
            ->where(function ($q) use ($now) {
                $q->whereNotNull('end_at')->where('end_at', '>=', $now)
                    ->orWhere(function ($qq) use ($now) {
                        $qq->whereNull('end_at')->where('start_at', '>=', $now);
                    });
            })
            ->count();

        return [
            Stat::make('Jumlah Admin', number_format($totalAdmins))
                ->icon(Heroicon::OutlinedShieldCheck)
                ->color('primary'),
            Stat::make('Jumlah User', number_format($totalUsers))
                ->icon(Heroicon::OutlinedUsers)
                ->color('gray'),
            Stat::make('Jumlah Events Semua', number_format($totalEvents))
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('info'),
            Stat::make('Event telah tamat', number_format($endedEvents))
                ->icon(Heroicon::OutlinedClock)
                ->color('warning'),
            Stat::make('Event batal', number_format($cancelledEvents))
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger'),
            Stat::make('Event masih aktif', number_format($activeEvents))
                ->icon(Heroicon::OutlinedBolt)
                ->color('success'),
        ];
    }
}

