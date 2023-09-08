<?php

namespace App\Domain\Strava;

use Carbon\CarbonInterval;

class WeekDayStatistics
{
    private \DateTimeImmutable $startDate;

    private function __construct(
        /** @var \App\Domain\Strava\Activity\Activity[] */
        private readonly array $activities,
    ) {
        $this->startDate = new \DateTimeImmutable();
        foreach ($this->activities as $activity) {
            if ($activity->getStartDate() > $this->startDate) {
                continue;
            }
            $this->startDate = $activity->getStartDate();
        }
    }

    public static function fromActivities(array $activities): self
    {
        return new self($activities);
    }

    public function getData(): array
    {
        $statistics = [];

        foreach ([1, 2, 3, 4, 5, 6, 0] as $weekDay) {
            $statistics[$weekDay] = [
                'numberOfRides' => 0,
                'totalDistance' => 0,
                'movingTime' => 0,
                'percentage' => 0,
            ];
        }

        foreach ($this->activities as $activity) {
            $weekDay = $activity->getStartDate()->format('w');

            ++$statistics[$weekDay]['numberOfRides'];
            $statistics[$weekDay]['totalDistance'] += $activity->getDistance();
            $statistics[$weekDay]['movingTime'] += $activity->getMovingTime();
            $statistics[$weekDay]['percentage'] = round($statistics[$weekDay]['numberOfRides'] / count($this->activities) * 100);
        }

        $data = [];
        foreach ($statistics as $statistic) {
            $movingTime = CarbonInterval::seconds($statistic['movingTime'])->cascade()->forHumans(['short' => true, 'minimumUnit' => 'minute']);
            $data[] = [count($data), $statistic['percentage'], $statistic['totalDistance'], $movingTime];
        }

        return $data;
    }
}
