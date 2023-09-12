<?php

namespace App\Domain\Strava\Activity\BuildActivityHeatmapChart;

final readonly class ActivityHeatMap
{
    private function __construct(
        /** @var \App\Domain\Strava\Activity\Activity[] */
        private array $activities,
    ) {
    }

    public function getData(int $year): array
    {
        $data = $rawData = [];
        foreach ($this->activities as $activity) {
            if (!$intensity = $activity->getIntensity()) {
                continue;
            }

            $day = $activity->getStartDate()->format('Y-m-d');
            $rawData[$day][] = $intensity;
        }

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod(
            \DateTimeImmutable::createFromFormat('d-m-Y', '01-01-'.$year),
            $interval,
            \DateTimeImmutable::createFromFormat('d-m-Y', '31-12-'.$year),
        );

        foreach ($period as $dt) {
            $day = $dt->format('Y-m-d');
            if (!array_key_exists($day, $rawData)) {
                $data[] = [$day, 0];

                continue;
            }
            $data[] = [$day, array_sum($rawData[$day]) / count($rawData[$day])];
        }

        return $data;
    }

    public static function fromActivities(array $activities): self
    {
        return new self($activities);
    }
}
