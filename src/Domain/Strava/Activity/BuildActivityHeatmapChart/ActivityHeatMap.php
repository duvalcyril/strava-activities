<?php

namespace App\Domain\Strava\Activity\BuildActivityHeatmapChart;

use App\Infrastructure\ValueObject\Time\SerializableDateTime;

final readonly class ActivityHeatMap
{
    private function __construct(
        /** @var \App\Domain\Strava\Activity\Activity[] */
        private array $activities,
    ) {
    }

    public function getData(
        SerializableDateTime $fromDate,
        SerializableDateTime $toDate,
    ): array {
        $data = $rawData = [];
        foreach ($this->activities as $activity) {
            if (!$intensity = $activity->getIntensity()) {
                continue;
            }

            $day = $activity->getStartDate()->format('Y-m-d');
            if (!array_key_exists($day, $rawData)) {
                $rawData[$day] = 0;
            }

            $rawData[$day] += $intensity;
        }

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod(
            $fromDate,
            $interval,
            $toDate,
        );

        foreach ($period as $dt) {
            $day = $dt->format('Y-m-d');
            if (!array_key_exists($day, $rawData)) {
                $data[] = [$day, 0];

                continue;
            }

            $data[] = [$day, $rawData[$day]];
        }

        return $data;
    }

    public static function fromActivities(array $activities): self
    {
        return new self($activities);
    }
}
