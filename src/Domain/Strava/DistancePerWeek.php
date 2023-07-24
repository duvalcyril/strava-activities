<?php

namespace App\Domain\Strava;

class DistancePerWeek
{
    private \DateTimeImmutable $startDate;

    private function __construct(
        /** @var \App\Domain\Strava\Activity\Activity[] */
        private readonly array $activities,
        private readonly \DateTimeImmutable $now,
    ) {
        $this->startDate = new \DateTimeImmutable();
        foreach ($this->activities as $activity) {
            if ($activity->getStartDate() > $this->startDate) {
                continue;
            }
            $this->startDate = $activity->getStartDate();
        }
    }

    public static function fromActivities(array $activities, \DateTimeImmutable $now): self
    {
        return new self($activities, $now);
    }

    public function getData(): array
    {
        $interval = new \DateInterval('P1W');
        $period = new \DatePeriod($this->startDate, $interval, $this->now);

        $distancePerWeek = [];

        foreach ($period as $date) {
            $distancePerWeek[$date->format('YW')] = [
                $date->modify('monday this week')->format('Y-m-d'),
                0,
            ];
        }

        foreach ($this->activities as $activity) {
            $week = $activity->getStartDate()->format('YW');
            $distancePerWeek[$week][1] += $activity->getDistance();
        }

        return array_values($distancePerWeek);
    }
}
