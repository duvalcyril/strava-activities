<?php

namespace App\Domain\Strava\Activity;

use Carbon\CarbonInterval;

class ActivityTotals
{
    private \DateTimeImmutable $startDate;

    private function __construct(
        private readonly array $activities,
        private readonly \DateTimeImmutable $now,
    ) {
        $this->startDate = new \DateTimeImmutable();
        foreach ($this->activities as $activity) {
            /* @var \App\Domain\Strava\Activity\Activity $activity */
            if ($activity->getStartDate() > $this->startDate) {
                continue;
            }
            $this->startDate = $activity->getStartDate();
        }
    }

    public function getDistance(): float
    {
        return array_sum(array_map(fn (Activity $activity) => $activity->getDistance(), $this->activities));
    }

    public function getElevation(): int
    {
        return array_sum(array_map(fn (Activity $activity) => $activity->getElevation(), $this->activities));
    }

    public function getCalories(): int
    {
        return array_sum(array_map(fn (Activity $activity) => $activity->getCalories(), $this->activities));
    }

    public function getMovingTimeFormatted(): string
    {
        $seconds = array_sum(array_map(fn (Activity $activity) => $activity->getMovingTime(), $this->activities));

        return CarbonInterval::seconds($seconds)->cascade()->forHumans(null, true);
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getDailyAverage(): float
    {
        $diff = $this->getStartDate()->diff($this->now);

        return $this->getDistance() / $diff->days;
    }

    public function getWeeklyAverage(): float
    {
        $diff = $this->getStartDate()->diff($this->now);

        return $this->getDistance() / ceil($diff->days / 7);
    }

    public function getMonthlyAverage(): float
    {
        $diff = $this->getStartDate()->diff($this->now);

        return $this->getDistance() / ($diff->m + 1);
    }

    public static function fromActivities(array $activities, \DateTimeImmutable $now): self
    {
        return new self($activities, $now);
    }
}
