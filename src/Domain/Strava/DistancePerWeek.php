<?php

namespace App\Domain\Strava;

use App\Infrastructure\ValueObject\Time\SerializableDateTime;

final class DistancePerWeek
{
    private SerializableDateTime $startDate;

    private function __construct(
        /** @var \App\Domain\Strava\Activity\Activity[] */
        private readonly array $activities,
        private readonly SerializableDateTime $now,
    ) {
        $this->startDate = new SerializableDateTime();
        foreach ($this->activities as $activity) {
            if ($activity->getStartDate()->isAfterOrOn($this->startDate)) {
                continue;
            }
            $this->startDate = $activity->getStartDate();
        }
    }

    public static function fromActivities(array $activities, SerializableDateTime $now): self
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
