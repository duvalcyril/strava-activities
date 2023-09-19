<?php

namespace App\Domain\Strava\BuildReadMe;

use App\Domain\Strava\Activity\ActivityTotals;
use App\Domain\Strava\Activity\BuildEddingtonChart\Eddington;
use App\Domain\Strava\Activity\StravaActivityRepository;
use App\Domain\Strava\BikeStatistics;
use App\Domain\Strava\Challenge\StravaChallengeRepository;
use App\Domain\Strava\Gear\StravaGearRepository;
use App\Domain\Strava\MonthlyStatistics;
use App\Domain\Strava\PowerOutputs;
use App\Domain\Strava\Trivia;
use App\Domain\Strava\WeekDayStatistics;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Environment\Settings;
use App\Infrastructure\ValueObject\Time\SerializableDateTime;
use Lcobucci\Clock\Clock;
use Twig\Environment;

#[AsCommandHandler]
final readonly class BuildReadMeCommandHandler implements CommandHandler
{
    public function __construct(
        private StravaActivityRepository $stravaActivityRepository,
        private StravaChallengeRepository $stravaChallengeRepository,
        private StravaGearRepository $stravaGearRepository,
        private Environment $twig,
        private Clock $clock,
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildReadMe);

        $allActivities = $this->stravaActivityRepository->findAll();

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
                SerializableDateTime::fromDateTimeImmutable($this->clock->now()),
            ),
            'allActivities' => $this->twig->load('strava-activities.html.twig')->render([
                'activities' => $allActivities,
                'addLinkToPowerOutputs' => true,
            ]),
            'monthlyStatistics' => MonthlyStatistics::fromActivitiesAndChallenges(
                $allActivities,
                $allChallenges,
                SerializableDateTime::fromDateTimeImmutable($this->clock->now()),
            ),
            'weekdayStatistics' => WeekDayStatistics::fromActivities(
                $allActivities,
            ),
            'bikeStatistics' => BikeStatistics::fromActivitiesAndGear($allActivities, $allBikes),
            'powerOutputs' => PowerOutputs::fromActivities(
                $allActivities
            ),
            'challenges' => $allChallenges,
            'trivia' => Trivia::fromActivities($allActivities),
            'eddington' => Eddington::fromActivities($allActivities),
        ]));
    }
}
