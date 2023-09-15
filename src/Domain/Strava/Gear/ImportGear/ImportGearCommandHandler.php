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
use GuzzleHttp\Exception\ClientException;
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
        $command->getOutput()->writeln('Importing gear...');

        $gearIds = array_unique(array_filter(array_map(
            fn (Activity $activity) => $activity->getGearId(),
            $this->stravaActivityRepository->findAll()
        )));

        foreach ($gearIds as $gearId) {
            try {
                $stravaGear = $this->strava->getGear($gearId);
            } catch (ClientException $exception) {
                if (429 !== $exception->getResponse()?->getStatusCode()) {
                    // Re-throw, we only want to catch "429 Too Many Requests".
                    throw $exception;
                }
                // This will allow initial imports with a lot of activities to proceed the next day.
                // This occurs when we exceed Strava API rate limits.
                $command->getOutput()->writeln('<error>You reached Strava API rate limits. You will need to import the rest of your activities tomorrow</error>');

                break;
            }

            try {
                $gear = $this->stravaGearRepository->findOneBy($gearId);
                $gear->updateDistance($stravaGear['distance'], $stravaGear['converted_distance']);
            } catch (EntityNotFound) {
                $gear = Gear::create(
                    $stravaGear,
                    $this->clock->now()
                );
            }
            $command->getOutput()->writeln(sprintf('  => Imported/updated gear "%s"', $gear->getName()));
            $this->stravaGearRepository->save($gear);
        }
    }
}
