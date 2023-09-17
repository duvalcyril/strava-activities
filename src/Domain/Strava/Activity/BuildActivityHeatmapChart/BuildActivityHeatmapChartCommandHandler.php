<?php

namespace App\Domain\Strava\Activity\BuildActivityHeatmapChart;

use App\Domain\Strava\Activity\Activity;
use App\Domain\Strava\Activity\StravaActivityRepository;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Environment\Settings;
use App\Infrastructure\Serialization\Json;
use App\Infrastructure\ValueObject\Time\SerializableDateTime;
use Lcobucci\Clock\Clock;

#[AsCommandHandler]
final readonly class BuildActivityHeatmapChartCommandHandler implements CommandHandler
{
    public function __construct(
        private StravaActivityRepository $stravaActivityRepository,
        private Clock $clock
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildActivityHeatmapChart);

        // We want a heatmap of the last twelve months.
        $now = SerializableDateTime::fromDateTimeImmutable($this->clock->now());
        $fromDate = SerializableDateTime::createFromFormat('Y-m-d', $now->modify('-11 months')->format('Y-m-01'));
        $toDate = SerializableDateTime::createFromFormat('Y-m-d', $now->format('Y-m-t'));

        \Safe\file_put_contents(
            Settings::getAppRoot().'/build/chart-activities-heatmap.json',
            Json::encode([
                'backgroundColor' => '#ffffff',
                'title' => [
                    'left' => 'center',
                    'text' => sprintf('%s - %s', $fromDate->format('M Y'), $toDate->format('M Y')),
                ],
                'tooltip' => [
                ],
                'visualMap' => [
                    'type' => 'piecewise',
                    'left' => 'center',
                    'bottom' => 0,
                    'orient' => 'horizontal',
                    'pieces' => [
                        [
                            'min' => 0,
                            'max' => 0,
                            'color' => '#cdd9e5',
                            'label' => 'No activities',
                        ],
                        [
                            'min' => 1,
                            'max' => 75,
                            'color' => '#68B34B',
                            'label' => 'Low',
                        ],
                        [
                            'min' => 76,
                            'max' => 125,
                            'color' => '#FAB735',
                            'label' => 'Medium',
                        ],
                        [
                            'min' => 126,
                            'max' => 200,
                            'color' => '#FF8E14',
                            'label' => 'High',
                        ],
                        [
                            'min' => 200,
                            'color' => '#FF0C0C',
                            'label' => 'Very high',
                        ],
                    ],
                ],
                'calendar' => [
                    'left' => 40,
                    'cellSize' => [
                        'auto',
                        13,
                    ],
                    'range' => [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')],
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
                    'data' => ActivityHeatMap::fromActivities(
                        array_filter(
                            $this->stravaActivityRepository->findAll(),
                            fn (Activity $activity) => $activity->getStartDate()->isAfterOrOn($fromDate) && $activity->getStartDate()->isBeforeOrOn($toDate)
                        ),
                    )->getData($fromDate, $toDate),
                ],
            ], JSON_PRETTY_PRINT),
        );
    }
}
