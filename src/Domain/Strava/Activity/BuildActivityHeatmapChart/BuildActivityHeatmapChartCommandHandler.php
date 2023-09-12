<?php

namespace App\Domain\Strava\Activity\BuildActivityHeatmapChart;

use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Environment\Settings;
use App\Infrastructure\Serialization\Json;

#[AsCommandHandler]
class BuildActivityHeatmapChartCommandHandler implements CommandHandler
{
    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildActivityHeatmapChart);

        \Safe\file_put_contents(
            Settings::getAppRoot().'/build/chart-activities-heatmap.json',
            Json::encode([
                'backgroundColor' => '#ffffff',
                'title' => [
                    'show' => false,
                    'left' => 'center',
                    'text' => 'Activity frequency 2023',
                ],
                'tooltip' => [
                ],
                'visualMap' => [
                    'show' => false,
                    'min' => 0,
                    'max' => 2,
                    'type' => 'piecewise',
                    'pieces' => [
                        [
                            'min' => 0,
                            'max' => 0,
                            'color' => '#cdd9e5',
                        ],
                        [
                            'min' => 1,
                            'max' => 1,
                            'color' => '#006d32',
                        ],
                        [
                            'min' => 2,
                            'max' => 2,
                            'color' => '#39d353',
                        ],
                    ],
                ],
                'calendar' => [
                    'left' => 40,
                    'cellSize' => [
                        'auto',
                        13,
                    ],
                    'range' => '2016', // @TODO: fix
                    'itemStyle' => [
                        'borderWidth' => 3,
                        'opacity' => 0,
                    ],
                    'splitLine' => [
                        'show' => false,
                    ],
                    'yearLabel' => [
                        'show' => false,
                    ],
                    'dayLabel' => [
                        'firstDay' => 1,
                        'align' => 'right',
                        'fontSize' => 10,
                        'nameMap' => [
                            'Sun',
                            'Mon',
                            'Tue',
                            'Wed',
                            'Thu',
                            'Fri',
                            'Sat',
                        ],
                    ],
                ],
                'series' => [
                    'type' => 'heatmap',
                    'coordinateSystem' => 'calendar',
                    'data' => [
                    ],
                ],
            ], JSON_PRETTY_PRINT),
        );
    }
}
