<?php

namespace App\Domain\Strava;

use App\Infrastructure\Exception\EntityNotFound;
use SleekDB\Store;

class StravaActivityRepository
{
    public function __construct(
        private readonly Store $store
    ) {
    }

    /**
     * @return \App\Domain\Strava\Activity[]
     */
    public function findAll(): array
    {
        return array_map(
            fn (array $row) => Activity::fromMap($row),
            $this->store->findAll(['_id' => 'desc'])
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
