<?php

namespace App\Domain\Strava\Activity;

use App\Domain\Strava\Gear\StravaGearRepository;
use App\Infrastructure\Exception\EntityNotFound;
use SleekDB\Store;

class StravaActivityRepository
{
    public function __construct(
        private readonly Store $store,
        private readonly StravaGearRepository $stravaGearRepository,
    ) {
    }

    /**
     * @return \App\Domain\Strava\Activity\Activity[]
     */
    public function findAll(int $limit = null): array
    {
        return array_map(
            fn (array $row) => Activity::fromMap($row),
            $this->store->findAll(['start_date_timestamp' => 'desc'], $limit)
        );
    }

    public function findOneBy(int $id): Activity
    {
        if (!$row = $this->store->findOneBy(['id', '==', $id])) {
            throw new EntityNotFound(sprintf('Activity "%s" not found', $id));
        }

        return Activity::fromMap($row);
    }

    public function add(Activity $activity): void
    {
        $this->store->insert($activity->jsonSerialize());
    }
}
