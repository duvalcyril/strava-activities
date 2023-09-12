<?php

namespace App\Domain\Strava\Activity;

use App\Infrastructure\Exception\EntityNotFound;
use SleekDB\Store;

readonly class StravaActivityRepository
{
    public function __construct(
        private Store $store
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

    /**
     * @return \App\Domain\Strava\Activity\Activity[]
     */
    public function findAllWithPower(): array
    {
        return array_map(
            fn (array $row) => Activity::fromMap($row),
            $this->store->findBy([
                function ($row) {
                    return !empty($row['streams'][StreamType::WATTS->value][0]['data']);
                },
            ], ['start_date_timestamp' => 'desc'])
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
