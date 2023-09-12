<?php

namespace App\Domain\Strava\Gear\ImportGear;

use App\Domain\Strava\Activity\Activity;
use App\Domain\Strava\Activity\StravaActivityRepository;
use App\Domain\Strava\Gear\Gear;
use App\Domain\Strava\Gear\StravaGearRepository;
use App\Domain\Strava\Strava;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Exception\EntityNotFound;
use Lcobucci\Clock\Clock;

#[AsCommandHandler]
final readonly class ImportGearCommandHandler implements CommandHandler
{
    public function __construct(
        private Strava $strava,
        private StravaActivityRepository $stravaActivityRepository,
        private StravaGearRepository $stravaGearRepository,
        private Clock $clock
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof ImportGear);

        $gearIds = array_unique(array_filter(array_map(
            fn (Activity $activity) => $activity->getGearId(),
            $this->stravaActivityRepository->findAll()
        )));

        foreach ($gearIds as $gearId) {
            $stravaGear = $this->strava->getGear($gearId);
            try {
                $gear = $this->stravaGearRepository->findOneBy($gearId);
                $gear->updateDistance($stravaGear['distance'], $stravaGear['converted_distance']);
            } catch (EntityNotFound) {
                $gear = Gear::create(
                    $stravaGear,
                    $this->clock->now()
                );
            }
            $this->stravaGearRepository->save($gear);
        }
    }
}
