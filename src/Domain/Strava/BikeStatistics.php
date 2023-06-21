<?php

namespace App\Domain\Strava;

use App\Domain\Strava\Activity\Activity;
use App\Domain\Strava\Gear\Gear;

class BikeStatistics
{
    private function __construct(
        /** @var \App\Domain\Strava\Activity\Activity[] */
        private readonly array $activities,
        /** @var \App\Domain\Strava\Gear\Gear[] */
        private readonly array $bikes,
    ) {
    }

    public static function fromActivitiesAndGear(array $activities, array $gear): self
    {
        return new self($activities, $gear);
    }

    public function getRows(): array
    {
        $statistics = array_map(fn (Gear $bike) => [
            'name' => sprintf('%s%s', $bike->getName(), $bike->isRetired() ? ' ☠️' : ''),
            'distance' => $bike->getDistance(),
            'numberOfRides' => count(array_filter($this->activities, fn (Activity $activity) => $activity->getGearId() == $bike->getId())),
        ], $this->bikes);

        $statistics[] = [
            'name' => 'Other',
            'distance' => array_sum(array_map(fn (Activity $activity) => $activity->getDistance(), $this->activities)) -
                array_sum(array_map(fn (Gear $bike) => $bike->getDistance(), $this->bikes)),
            'numberOfRides' => count(array_filter($this->activities, fn (Activity $activity) => empty($activity->getGearId()))),
        ];

        return $statistics;
    }
}
