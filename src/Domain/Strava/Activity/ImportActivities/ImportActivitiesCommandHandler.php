<?php

namespace App\Domain\Strava\Activity\ImportActivities;

use App\Domain\Strava\Activity\Activity;
use App\Domain\Strava\Activity\ActivityType;
use App\Domain\Strava\Activity\StravaActivityRepository;
use App\Domain\Strava\Activity\StreamType;
use App\Domain\Strava\Strava;
use App\Domain\Weather\OpenMeteo\OpenMeteo;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Exception\EntityNotFound;
use League\Flysystem\Filesystem;
use Ramsey\Uuid\Rfc4122\UuidV5;

#[AsCommandHandler]
final readonly class ImportActivitiesCommandHandler implements CommandHandler
{
    public function __construct(
        private Strava $strava,
        private OpenMeteo $openMeteo,
        private StravaActivityRepository $stravaActivityRepository,
        private Filesystem $filesystem,
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof ImportActivities);

        $athlete = $this->strava->getAthlete();

        foreach ($this->strava->getActivities() ?? [] as $stravaActivity) {
            if (!$activityType = ActivityType::tryFrom($stravaActivity['type'])) {
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

                $localImagePaths = [];
                $photos = $this->strava->getActivityPhotos($activity->getId());
                foreach ($photos as $photo) {
                    if (empty($photo['urls'][5000])) {
                        continue;
                    }

                    $extension = pathinfo($photo['urls'][5000], PATHINFO_EXTENSION);

                    $imagePath = sprintf('files/activities/%s.%s', UuidV5::uuid1(), $extension);
                    $this->filesystem->write(
                        $imagePath,
                        $this->strava->downloadImage($photo['urls'][5000])
                    );
                    $localImagePaths[] = $imagePath;
                }
                $activity->updateLocalImagePaths($localImagePaths);

                if ($activityType->supportsWeather() && $activity->getLatitude() && $activity->getLongitude()) {
                    $weather = $this->openMeteo->getWeatherStats(
                        $activity->getLatitude(),
                        $activity->getLongitude(),
                        $activity->getStartDate()
                    );
                    $activity->updateWeather($weather);
                }

                $this->stravaActivityRepository->add($activity);
                // Try to avoid Strava rate limits.
                sleep(20);
            }
        }
    }
}
