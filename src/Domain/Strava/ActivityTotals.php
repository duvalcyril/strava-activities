<?php

namespace App\Domain\Strava;

use Carbon\CarbonInterval;

class ActivityTotals
{
    private function __construct(
        private readonly array $activities
    ) {
    }

    public function getDistance(): float
    {
        return array_sum(array_map(fn (Activity $activity) => $activity->getDistance(), $this->activities));
    }

    public function getElevation(): int
    {
        return array_sum(array_map(fn (Activity $activity) => $activity->getElevation(), $this->activities));
    }

    public function getMovingTimeFormatted(): string
    {
        $seconds = array_sum(array_map(fn (Activity $activity) => $activity->getMovingTime(), $this->activities));

        return CarbonInterval::seconds($seconds)->cascade()->forHumans(null, true);
    }

    public static function fromActivities(array $activities): self
    {
        return new self($activities);
    }
}
