<?php

namespace App\Domain\Strava\Activity\BuildEddingtonChart;

use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Environment\Settings;
use App\Infrastructure\Serialization\Json;

#[AsCommandHandler]
final readonly class BuildEddingtonChartCommandHandler implements CommandHandler
{
    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildEddingtonChart);

        \Safe\file_put_contents(
            Settings::getAppRoot().'/build/chart-activities-eddington.json',
            Json::encode([
                'animation' => false,
                'grid' => [
                    'left' => '3%',
                    'right' => '4%',
                    'bottom' => '3%',
                    'containLabel' => true,
                ],
                'legend' => [
                    'show' => true,
                ],
                'xAxis' => [
                    'data' => ['1km', '2km', '3km', '4km', '5km', '6km', '7km'],
                    'type' => 'category',
                    'axisTick' => [
                        'alignWithLabel' => true,
                    ],
                ],
                'yAxis' => [
                    [
                        'type' => 'value',
                        'splitLine' => [
                            'show' => true,
                        ],
                    ],
                    [
                        'type' => 'value',
                        'splitLine' => [
                            'show' => false,
                        ],
                        'max' => 120,
                    ],
                ],
                'series' => [
                    [
                        'name' => 'Times completed',
                        'yAxisIndex' => 0,
                        'type' => 'bar',
                        'label' => [
                            'show' => false,
                        ],
                        'showBackground' => false,
                        'itemStyle' => [
                            'color' => 'rgba(227, 73, 2, 0.3)',
                        ],
                        'data' => [
                            100,
                            89,
                            [
                                'value' => 120,
                                'itemStyle' => [
                                    'color' => 'rgba(227, 73, 2, 0.8)',
                                ],
                            ],
                            60,
                            20,
                            10,
                            3,
                        ],
                    ],
                    [
                        'name' => 'Eddington',
                        'yAxisIndex' => 1,
                        'zlevel' => 1,
                        'type' => 'line',
                        'label' => [
                            'show' => false,
                        ],
                        'showBackground' => false,
                        'itemStyle' => [
                            'color' => '#E34902',
                        ],
                        'data' => [
                            0,
                            1,
                            2,
                            3,
                            4,
                            5,
                            6,
                        ],
                    ],
                ],
            ], JSON_PRETTY_PRINT),
        );
    }
}
