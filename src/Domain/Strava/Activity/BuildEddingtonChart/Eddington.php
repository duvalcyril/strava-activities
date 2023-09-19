<?php

namespace App\Domain\Strava\Activity\BuildEddingtonChart;

final class Eddington
{
    private static $distancesPerDay = [];

    private function __construct(
        /** @var \App\Domain\Strava\Activity\Activity[] */
        private readonly array $activities,
    ) {
    }

    private function getDistancesPerDay(): array
    {
        if (!empty(Eddington::$distancesPerDay)) {
            return Eddington::$distancesPerDay;
        }

        Eddington::$distancesPerDay = [];
        foreach ($this->activities as $activity) {
            $day = $activity->getStartDate()->format('dmY');
            if (!array_key_exists($day, Eddington::$distancesPerDay)) {
                Eddington::$distancesPerDay[$day] = 0;
            }
            Eddington::$distancesPerDay[$day] += $activity->getDistance();
        }

        Eddington::$distancesPerDay = array_values(Eddington::$distancesPerDay);

        return Eddington::$distancesPerDay;
    }

    public function getLongestDistanceInADay(): int
    {
        return round(max($this->getDistancesPerDay()));
    }

    public function getTimesCompletedData(): array
    {
        $data = [];
        for ($distance = 1; $distance <= $this->getLongestDistanceInADay(); ++$distance) {
            // We need to count the number of days we exceeded this distance.
            $data[] = count(array_filter($this->getDistancesPerDay(), fn (float $distanceForDay) => $distanceForDay >= $distance));
        }

        return $data;
    }

    public function getNumber(): int
    {
        $number = 1;
        for ($distance = 1; $distance <= $this->getLongestDistanceInADay(); ++$distance) {
            $timesCompleted = count(array_filter($this->getDistancesPerDay(), fn (float $distanceForDay) => $distanceForDay >= $distance));
            if ($timesCompleted < $distance) {
                break;
            }
            $number = $distance;
        }

        return $number;
    }

    public function getRidesToCompleteForFutureNumbers(): array
    {
        $futureNumbers = [];
        $eddingtonNumber = $this->getNumber();
        for ($distance = $eddingtonNumber + 1; $distance <= $this->getLongestDistanceInADay(); ++$distance) {
            $timesCompleted = count(array_filter($this->getDistancesPerDay(), fn (float $distanceForDay) => $distanceForDay >= $distance));
            $futureNumbers[$distance] = $distance - $timesCompleted;
        }

        return $futureNumbers;
    }

    public static function fromActivities(array $activities): self
    {
        return new self($activities);
    }
}
