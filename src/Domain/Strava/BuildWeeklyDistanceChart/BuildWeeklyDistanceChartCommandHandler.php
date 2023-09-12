<?php

namespace App\Domain\Strava\BuildWeeklyDistanceChart;

use App\Domain\Strava\Activity\StravaActivityRepository;
use App\Domain\Strava\DistancePerWeek;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Environment\Settings;
use App\Infrastructure\Serialization\Json;
use Lcobucci\Clock\Clock;

#[AsCommandHandler]
final readonly class BuildWeeklyDistanceChartCommandHandler implements CommandHandler
{
    public function __construct(
        private StravaActivityRepository $stravaActivityRepository,
        private Clock $clock,
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildWeeklyDistanceChart);

        $allActivities = $this->stravaActivityRepository->findAll();
        \Safe\file_put_contents(
            Settings::getAppRoot().'/build/chart.json',
            Json::encode([
                'animation' => false,
                'backgroundColor' => '#ffffff',
                'color' => [
                    '#E34902',
                ],
                'grid' => [
                    'left' => '3%',
                    'right' => '4%',
                    'bottom' => '3%',
                    'containLabel' => true,
                ],
                'xAxis' => [
                    [
                        'type' => 'time',
                        'boundaryGap' => false,
                        'axisTick' => [
                            'show' => false,
                        ],
                        'axisLabel' => [
                            'formatter' => [
                                'year' => '{yyyy}',
                                'month' => '{MMM}',
                                'day' => '',
                                'hour' => '{HH}:{mm}',
                                'minute' => '{HH}:{mm}',
                                'second' => '{HH}:{mm}:{ss}',
                                'millisecond' => '{hh}:{mm}:{ss} {SSS}',
                                'none' => '{yyyy}-{MM}-{dd} {hh}:{mm}:{ss} {SSS}',
                            ],
                        ],
                        'splitLine' => [
                            'show' => true,
                            'lineStyle' => [
                                'color' => '#E0E6F1',
                            ],
                        ],
                    ],
                ],
                'yAxis' => [
                    [
                        'type' => 'value',
                        'splitLine' => [
                            'show' => false,
                        ],
                        'axisLabel' => [
                            'formatter' => '{value} km',
                        ],
                    ],
                ],
                'series' => [
                    [
                        'name' => 'Average distance / week',
                        'type' => 'line',
                        'smooth' => false,
                        'label' => [
                            'show' => true,
                            'formatter' => '{@[1]} km',
                            'rotate' => -45,
                        ],
                        'lineStyle' => [
                            'width' => 1,
                        ],
                        'symbolSize' => 6,
                        'showSymbol' => true,
                        'areaStyle' => [
                            'opacity' => 0.3,
                            'color' => 'rgba(227, 73, 2, 0.3)',
                        ],
                        'emphasis' => [
                            'focus' => 'series',
                        ],
                        'data' => DistancePerWeek::fromActivities(
                            $allActivities,
                            $this->clock->now(),
                        )->getData(),
                    ],
                ],
            ], JSON_PRETTY_PRINT),
        );
    }
}
