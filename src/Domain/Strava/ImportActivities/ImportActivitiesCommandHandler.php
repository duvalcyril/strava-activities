<?php

namespace App\Domain\Strava\ImportActivities;

use App\Domain\Strava\Activity\Activity;
use App\Domain\Strava\Activity\ActivityType;
use App\Domain\Strava\Activity\StravaActivityRepository;
use App\Domain\Strava\Activity\StreamType;
use App\Domain\Strava\Strava;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Exception\EntityNotFound;

#[AsCommandHandler]
class ImportActivitiesCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly Strava $strava,
        private readonly StravaActivityRepository $stravaActivityRepository,
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof ImportActivities);

        $athlete = $this->strava->getAthlete();

        foreach ($this->strava->getActivities() ?? [] as $stravaActivity) {
            if (!ActivityType::tryFrom($stravaActivity['type'])) {
                continue;
            }

            try {
                $this->stravaActivityRepository->findOneBy($stravaActivity['id']);
            } catch (EntityNotFound) {
                $streams = [];
                try {
                    $streams = $this->strava->getActivityStreams($stravaActivity['id'], StreamType::WATTS);
                } catch (\Throwable) {
                }

                $activity = Activity::create([
                    ...$this->strava->getActivity($stravaActivity['id']),
                    'streams' => [
                        'watts' => $streams,
                    ],
                    'athlete_weight' => $athlete['weight'],
                ]);

                $this->stravaActivityRepository->add($activity);
            }
        }
    }
}
