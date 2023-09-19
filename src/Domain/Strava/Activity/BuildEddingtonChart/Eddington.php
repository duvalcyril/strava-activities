<?php

namespace App\Domain\Strava\Activity\BuildEddingtonChart;

final readonly class Eddington
{
    private function __construct(
        /** @var \App\Domain\Strava\Activity\Activity[] */
        private array $activities,
    ) {
    }

    private function getDistancesPerDay(): array
    {
        $distancesPerDay = [];
        foreach ($this->activities as $activity) {
            $day = $activity->getStartDate()->format('dmY');
            if (!array_key_exists($day, $distancesPerDay)) {
                $distancesPerDay[$day] = 0;
            }
            $distancesPerDay[$day] += $activity->getDistance();
        }

        return array_values($distancesPerDay);
    }

    public function getLongestDistanceInADay(): int
    {
        return round(max($this->getDistancesPerDay()));
    }

    public function getTimesCompletedData(): array
    {
        $data = [];
        foreach (range(1, $this->getLongestDistanceInADay()) as $distance) {
            // We need to count the number of days we exceeded this distance.
            $data[] = count(array_filter($this->getDistancesPerDay(), fn (int $distanceForDay) => $distanceForDay >= $distance));
        }

        return $data;
    }

    public function getNumber(): int
    {
        $number = 1;
        foreach (range(1, $this->getLongestDistanceInADay()) as $distance) {
            $timesCompleted = count(array_filter($this->getDistancesPerDay(), fn (int $distanceForDay) => $distanceForDay >= $distance));
            if ($timesCompleted < $distance) {
                break;
            }
            $number = $distance;
        }

        return round($number);
    }

    public static function fromActivities(array $activities): self
    {
        return new self($activities);
    }
}
