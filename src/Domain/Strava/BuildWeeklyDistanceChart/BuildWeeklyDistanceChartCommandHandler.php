<?php

namespace App\Domain\Strava\BuildWeeklyDistanceChart;

use App\Domain\Strava\Activity\StravaActivityRepository;
use App\Domain\Strava\DistancePerWeek;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Environment\Settings;
use Lcobucci\Clock\Clock;
use Twig\Environment;

#[AsCommandHandler]
class BuildWeeklyDistanceChartCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly StravaActivityRepository $stravaActivityRepository,
        private readonly Environment $twig,
        private readonly Clock $clock,
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildWeeklyDistanceChart);

        $allActivities = $this->stravaActivityRepository->findAll();
        \Safe\file_put_contents(
            Settings::getAppRoot().'/build/chart.json',
            $this->twig->load('strava-weekly-distance-chart.html.twig')->render([
                'data' => DistancePerWeek::fromActivities(
                    $allActivities,
                    $this->clock->now(),
                )->getData(),
            ])
        );
    }
}
