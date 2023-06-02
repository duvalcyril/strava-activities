<?php

namespace App\Domain\Strava\Activity;

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

    public function getStartDate(): \DateTimeImmutable
    {
        $startDate = new \DateTimeImmutable();
        foreach ($this->activities as $activity) {
            /* @var \App\Domain\Strava\Activity\Activity $activity */
            if ($activity->getStartDate() > $startDate) {
                continue;
            }
            $startDate = $activity->getStartDate();
        }

        return $startDate;
    }

    public static function fromActivities(array $activities): self
    {
        return new self($activities);
    }
}
