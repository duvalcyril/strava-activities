<?php

namespace App\Domain\Strava;

use App\Domain\Strava\Activity\Activity;
use App\Domain\Strava\Challenge\Challenge;
use Carbon\CarbonInterval;

class MonthlyStatistics
{
    private \DateTimeImmutable $startDate;

    private function __construct(
        /** @var \App\Domain\Strava\Activity\Activity[] */
        private readonly array $activities,
        /** @var \App\Domain\Strava\Challenge\Challenge[] */
        private readonly array $challenges,
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

    public static function fromActivitiesAndChallenges(array $activities, array $challenges, \DateTimeImmutable $now): self
    {
        return new self($activities, $challenges, $now);
    }

    public function getRows(): array
    {
        $statistics = [];

        $interval = new \DateInterval('P1M');
        $period = new \DatePeriod($this->startDate, $interval, $this->now);

        foreach ($period as $date) {
            $month = $date->format('Ym');
            $statistics[$month] = [
                'month' => $date->format('F Y'),
                'numberOfRides' => 0,
                'totalDistance' => 0,
                'totalElevation' => 0,
                'movingTime' => 0,
                'challengesCompleted' => count(array_filter(
                    $this->challenges,
                    fn (Challenge $challenge) => $challenge->getCreatedOn()->format('Ym') == $date->format('Ym')
                )),
                'gears' => [],
            ];
        }

        $statistics = array_reverse($statistics, true);

        foreach ($this->activities as $activity) {
            $month = $activity->getStartDate()->format('Ym');

            if (!isset($statistics[$month]['gears'][$activity->getGearId()])) {
                $statistics[$month]['gears'][$activity->getGearId()] = [
                    'name' => $activity->getGearName(),
                    'distance' => 0,
                ];
            }

            ++$statistics[$month]['numberOfRides'];
            $statistics[$month]['totalDistance'] += $activity->getDistance();
            $statistics[$month]['totalElevation'] += $activity->getElevation();
            $statistics[$month]['movingTime'] += $activity->getMovingTime();
            $statistics[$month]['gears'][$activity->getGearId()]['distance'] += $activity->getDistance();

            // Sort gears by gears.
            $gears = $statistics[$month]['gears'];
            uasort($gears, function (array $a, array $b) {
                if ($a['distance'] == $b['distance']) {
                    return 0;
                }

                return ($a['distance'] < $b['distance']) ? 1 : -1;
            });
            $statistics[$month]['gears'] = $gears;
        }

        foreach ($statistics as &$statistic) {
            $statistic['movingTime'] = CarbonInterval::seconds($statistic['movingTime'])->cascade()->forHumans(['short' => true, 'minimumUnit' => 'minute']);
        }

        return $statistics;
    }

    public function getTotals(): array
    {
        return [
            'numberOfRides' => count($this->activities),
            'totalDistance' => array_sum(array_map(fn (Activity $activity) => $activity->getDistance(), $this->activities)),
            'totalElevation' => array_sum(array_map(fn (Activity $activity) => $activity->getElevation(), $this->activities)),
            'movingTime' => CarbonInterval::seconds(array_sum(array_map(fn (Activity $activity) => $activity->getMovingTime(), $this->activities)))->cascade()->forHumans(['short' => true, 'minimumUnit' => 'minute']),
            'challengesCompleted' => count($this->challenges),
        ];
    }
}
