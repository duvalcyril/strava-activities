<?php

namespace App\Domain\Strava;

use App\Infrastructure\ValueObject\Weight;
use Carbon\CarbonInterval;

class PowerOutputs
{
    private function __construct(
        /** @var \App\Domain\Strava\Activity\Activity[] */
        private readonly array $activities,
        private readonly Weight $weight,
    ) {
    }

    public static function fromActivitiesAndWeight(array $activities, Weight $weight): self
    {
        return new self($activities, $weight);
    }

    public function getBest(): array
    {
        $best = [];
        $timeIntervalsInSeconds = [
            5, 10, 30, 60, 300, 1200, 3600,
        ];

        foreach ($this->activities as $activity) {
            foreach ($timeIntervalsInSeconds as $timeIntervalInSeconds) {
                $power = $activity->getBestAveragePowerForTimeInterval($timeIntervalInSeconds);
                if (!isset($best[$timeIntervalInSeconds]) || $best[$timeIntervalInSeconds]['power'] < $power) {
                    $interval = CarbonInterval::seconds($timeIntervalInSeconds);
                    $best[$timeIntervalInSeconds] = [
                        'time' => (int) $interval->totalHours ? $interval->totalHours.' h' : ((int) $interval->totalMinutes ? $interval->totalMinutes.' m' : $interval->totalSeconds.' s'),
                        'power' => $power,
                        'relativePower' => round($power / $this->weight->getFloat(), 2),
                    ];
                }
            }
        }

        return $best;
    }
}
