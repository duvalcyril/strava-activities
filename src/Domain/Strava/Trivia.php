<?php

namespace App\Domain\Strava;

use App\Domain\Strava\Activity\Activity;

final readonly class Trivia
{
    private function __construct(
        /* @var \App\Domain\Strava\Activity\Activity[] */
        private array $activities,
    ) {
    }

    public static function fromActivities(array $activities): self
    {
        return new self($activities);
    }

    public function getTotalKudosReceived(): int
    {
        return array_sum(array_map(fn (Activity $activity) => $activity->getKudoCount(), $this->activities));
    }

    public function getMostKudotedActivity(): Activity
    {
        $mostKudotedActivity = reset($this->activities);
        foreach ($this->activities as $activity) {
            if ($activity->getKudoCount() < $mostKudotedActivity->getKudoCount()) {
                continue;
            }
            $mostKudotedActivity = $activity;
        }

        return $mostKudotedActivity;
    }

    public function getFirstActivity(): Activity
    {
        $fistActivity = reset($this->activities);
        foreach ($this->activities as $activity) {
            if ($activity->getStartDate() > $fistActivity->getStartDate()) {
                continue;
            }
            $fistActivity = $activity;
        }

        return $fistActivity;
    }

    public function getEarliestActivity(): Activity
    {
        $earliestActivity = reset($this->activities);
        foreach ($this->activities as $activity) {
            if ($activity->getStartDate()->getMinutesSinceStartOfDay() > $earliestActivity->getStartDate()->getMinutesSinceStartOfDay()) {
                continue;
            }
            $earliestActivity = $activity;
        }

        return $earliestActivity;
    }

    public function getLatestActivity(): Activity
    {
        $latestActivity = reset($this->activities);
        foreach ($this->activities as $activity) {
            if ($this->getMinutesSinceMidnight($activity->getStartDate()) < $this->getMinutesSinceMidnight($latestActivity->getStartDate())) {
                continue;
            }
            $latestActivity = $activity;
        }

        return $latestActivity;
    }

    public function getLongestActivity(): Activity
    {
        $longestActivity = reset($this->activities);
        foreach ($this->activities as $activity) {
            if ($activity->getDistance() < $longestActivity->getDistance()) {
                continue;
            }
            $longestActivity = $activity;
        }

        return $longestActivity;
    }

    public function getMostConsecutiveDaysOfCycling(): int
    {
        $mostConsecutiveDaysOfCycling = 0;
        foreach ($this->activities as $activity) {
        }
    }
}
