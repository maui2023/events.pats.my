<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Event;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class EventsMonthlyChart extends ChartWidget
{
    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Events (12 bulan)';

    protected string $color = 'primary';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $end = Carbon::now()->endOfMonth();
        $start = Carbon::now()->subMonths(11)->startOfMonth();

        $events = Event::query()
            ->whereBetween('start_at', [$start, $end])
            ->get(['start_at', 'is_published']);

        $labels = [];
        $published = [];
        $cancelled = [];

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m');
            $labels[] = $cursor->format('M Y');
            $published[$key] = 0;
            $cancelled[$key] = 0;
            $cursor->addMonth();
        }

        foreach ($events as $e) {
            $key = optional($e->start_at)->format('Y-m');
            if (!$key || (!array_key_exists($key, $published))) {
                continue;
            }
            if ($e->is_published) {
                $published[$key]++;
            } else {
                $cancelled[$key]++;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Published',
                    'data' => array_values($published),
                    'backgroundColor' => '#16a34a',
                ],
                [
                    'label' => 'Batal/Draft',
                    'data' => array_values($cancelled),
                    'backgroundColor' => '#ef4444',
                ],
            ],
        ];
    }
}

