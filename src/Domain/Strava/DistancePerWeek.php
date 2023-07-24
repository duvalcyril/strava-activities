<?php

namespace App\Domain\Strava;

class DistancePerWeek
{
    private function __construct(
        /** @var \App\Domain\Strava\Activity\Activity[] */
        private readonly array $activities,
    ) {
    }

    public static function fromActivities(array $activities): self
    {
        return new self($activities);
    }

    public function getData(): array
    {
        $distancePerWeek = [];
        foreach ($this->activities as $activity) {
            $week = $activity->getStartDate()->format('YW');
            if (!isset($distancePerWeek[$week])) {
                $distancePerWeek[$week] = [
                    $activity->getStartDate()->modify('monday this week')->format('Y-m-d'),
                    0,
                ];
            }
            $distancePerWeek[$week][1] += $activity->getDistance();
        }

        return array_values($distancePerWeek);
    }
}
