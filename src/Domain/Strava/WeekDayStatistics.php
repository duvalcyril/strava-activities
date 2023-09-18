<?php

namespace App\Domain\Strava;

use App\Domain\Strava\Activity\Activity;
use Carbon\CarbonInterval;

final readonly class WeekDayStatistics
{
    private function __construct(
        /** @var \App\Domain\Strava\Activity\Activity[] */
        private array $activities,
    ) {
    }

    public static function fromActivities(array $activities): self
    {
        return new self($activities);
    }

    public function getData(): array
    {
        $statistics = [];
        $totalMovingTime = array_sum(array_map(fn (Activity $activity) => $activity->getMovingTime(), $this->activities));

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
            $statistics[$weekDay]['percentage'] = round($statistics[$weekDay]['movingTime'] / $totalMovingTime * 100);
        }

        $data = [];
        foreach ($statistics as $statistic) {
            $movingTime = CarbonInterval::seconds($statistic['movingTime'])->cascade()->forHumans(['short' => true, 'minimumUnit' => 'minute']);
            $data[] = [count($data), $statistic['percentage'], $statistic['totalDistance'], $movingTime];
        }

        return $data;
    }
}
