<?php

namespace App\Domain\Strava\BuildWeekdayStatsChart;

use App\Domain\Strava\Activity\StravaActivityRepository;
use App\Domain\Strava\WeekDayStatistics;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Environment\Settings;
use Twig\Environment;

#[AsCommandHandler]
class BuildWeekdayStatsChartCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly StravaActivityRepository $stravaActivityRepository,
        private readonly Environment $twig,
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildWeekdayStatsChart);

        $weekdayStatistics = WeekDayStatistics::fromActivities(
            $this->stravaActivityRepository->findAll(),
        );
        $data = [];
        foreach ($weekdayStatistics->getRows() as $row) {
            $data[] = [count($data), $row['percentage'], $row['totalDistance'], $row['movingTime']];
        }
        \Safe\file_put_contents(
            Settings::getAppRoot().'/build/chart-weekday-stats.json',
            $this->twig->load('strava-weekday-stats-chart.html.twig')->render([
                'data' => $data,
            ])
        );
    }
}
