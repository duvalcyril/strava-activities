<?php

namespace App\Console;

use App\Domain\Strava\Activity\ActivityTotals;
use App\Domain\Strava\Activity\StravaActivityRepository;
use App\Domain\Strava\BikeStatistics;
use App\Domain\Strava\Challenge\StravaChallengeRepository;
use App\Domain\Strava\DistancePerWeek;
use App\Domain\Strava\Gear\StravaGearRepository;
use App\Domain\Strava\MonthlyStatistics;
use App\Domain\Strava\PowerOutputs;
use App\Domain\Strava\WeekDayStatistics;
use App\Infrastructure\Environment\Settings;
use Lcobucci\Clock\Clock;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

#[AsCommand(name: 'app:strava:build-files', description: 'Build Strava files')]
class BuildStravaActivityFilesConsoleCommand extends Command
{
    public function __construct(
        private readonly StravaActivityRepository $stravaActivityRepository,
        private readonly StravaChallengeRepository $stravaChallengeRepository,
        private readonly StravaGearRepository $stravaGearRepository,
        private readonly Environment $twig,
        private readonly Clock $clock,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $allActivities = $this->stravaActivityRepository->findAll();

        \Safe\file_put_contents(
            Settings::getAppRoot().'/build/strava-activities-latest.md',
            $this->twig->load('strava-activities.html.twig')->render([
                'activities' => $this->stravaActivityRepository->findAll(5),
                'addLinkToAllActivities' => true,
            ])
        );

        \Safe\file_put_contents(
            Settings::getAppRoot().'/build/chart.json',
            $this->twig->load('strava-weekly-distance-chart.html.twig')->render([
                'data' => DistancePerWeek::fromActivities(
                    $allActivities,
                    $this->clock->now(),
                )->getData(),
            ])
        );

        foreach ($allActivities as &$activity) {
            if (!$activity->getGearId()) {
                continue;
            }
            $activity->enrichWithGearName(
                $this->stravaGearRepository->findOneBy($activity->getGearId())->getName()
            );
        }
        $allChallenges = $this->stravaChallengeRepository->findAll();
        $allBikes = $this->stravaGearRepository->findAll();

        \Safe\file_put_contents(Settings::getAppRoot().'/README.md', $this->twig->load('readme.html.twig')->render([
            'totals' => ActivityTotals::fromActivities(
                $allActivities,
                $this->clock->now(),
            ),
            'allActivities' => $this->twig->load('strava-activities.html.twig')->render([
                'activities' => $allActivities,
            ]),
            'monthlyStatistics' => MonthlyStatistics::fromActivitiesAndChallenges(
                $allActivities,
                $allChallenges,
                $this->clock->now()
            ),
            'weekdayStatistics' => WeekDayStatistics::fromActivities(
                $allActivities,
            ),
            'bikeStatistics' => BikeStatistics::fromActivitiesAndGear($allActivities, $allBikes),
            'powerOutputs' => PowerOutputs::fromActivities(
                $allActivities
            ),
            'challenges' => $allChallenges,
        ]));

        return Command::SUCCESS;
    }
}
