<?php

namespace App\Domain\Strava;

use App\Domain\Strava\Activity\Activity;
use App\Domain\Strava\Challenge\Challenge;

class MonthlyStatistics
{
    private function __construct(
        /** @var \App\Domain\Strava\Activity\Activity[] */
        private readonly array $activities,
        /** @var \App\Domain\Strava\Challenge\Challenge[] */
        private readonly array $challenges,
    ) {
    }

    public static function fromActivitiesAndChallenges(array $activities, array $challenges): self
    {
        return new self($activities, $challenges);
    }

    public function getRows(): array
    {
        $statistics = [];

        foreach ($this->activities as $activity) {
            $month = $activity->getStartDate()->format('Ym');
            if (empty($statistics[$month])) {
                $statistics[$month] = [
                    'month' => $activity->getStartDate()->format('F Y'),
                    'numberOfRides' => 0,
                    'totalDistance' => 0,
                    'totalElevation' => 0,
                    'challengesCompleted' => count(array_filter(
                        $this->challenges,
                        fn (Challenge $challenge) => $challenge->getCreatedOn()->format('Ym') == $activity->getStartDate()->format('Ym')
                    )),
                ];
            }

            ++$statistics[$month]['numberOfRides'];
            $statistics[$month]['totalDistance'] += $activity->getDistance();
            $statistics[$month]['totalElevation'] += $activity->getElevation();
        }

        return $statistics;
    }

    public function getTotals(): array
    {
        return [
            'numberOfRides' => count($this->activities),
            'totalDistance' => array_sum(array_map(fn (Activity $activity) => $activity->getDistance(), $this->activities)),
            'totalElevation' => array_sum(array_map(fn (Activity $activity) => $activity->getElevation(), $this->activities)),
            'challengesCompleted' => count($this->challenges),
        ];
    }
}
