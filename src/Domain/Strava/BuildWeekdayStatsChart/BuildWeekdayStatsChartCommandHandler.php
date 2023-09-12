<?php

namespace App\Domain\Strava\BuildWeekdayStatsChart;

use App\Domain\Strava\Activity\StravaActivityRepository;
use App\Domain\Strava\WeekDayStatistics;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Environment\Settings;
use App\Infrastructure\Serialization\Json;

#[AsCommandHandler]
class BuildWeekdayStatsChartCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly StravaActivityRepository $stravaActivityRepository,
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildWeekdayStatsChart);

        $weekdayStatistics = WeekDayStatistics::fromActivities(
            $this->stravaActivityRepository->findAll(),
        );
        \Safe\file_put_contents(
            Settings::getAppRoot().'/build/chart-weekday-stats.json',
            Json::encode([
                'animation' => false,
                'grid' => [
                    'left' => '3%',
                    'right' => '4%',
                    'bottom' => '3%',
                    'containLabel' => true,
                ],
                'xAxis' => [
                    'type' => 'category',
                    'data' => [
                        'Mon',
                        'Tue',
                        'Wed',
                        'Thu',
                        'Fri',
                        'Sat',
                        'Sun',
                    ],
                ],
                'yAxis' => [
                    'type' => 'value',
                    'splitLine' => [
                        'show' => false,
                    ],
                    'axisLabel' => [
                        'show' => false,
                    ],
                ],
                'series' => [
                    [
                        'type' => 'bar',
                        'label' => [
                            'show' => true,
                            'position' => 'inside',
                            'formatter' => "{@[1]} rides\n{@[2]} km\n{@[3]}",
                        ],
                        'showBackground' => true,
                        'itemStyle' => [
                            'color' => '#E34902',
                        ],
                        'backgroundStyle' => [
                            'color' => 'rgba(227, 73, 2, 0.3)',
                        ],
                        'data' => $weekdayStatistics->getData(),
                    ],
                ],
            ], JSON_PRETTY_PRINT),
        );
    }
}
