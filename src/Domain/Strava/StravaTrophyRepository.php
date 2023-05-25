<?php

namespace App\Domain\Strava;

use App\Infrastructure\Exception\EntityNotFound;
use SleekDB\Store;

class StravaTrophyRepository
{
    public function __construct(
        private readonly Store $store
    ) {
    }

    /**
     * @return \App\Domain\Strava\Trophy[]
     */
    public function findAll(): array
    {
        return array_map(
            fn (array $row) => Trophy::fromMap($row),
            $this->store->findAll()
        );
    }

    public function findOneBy(int $id): Trophy
    {
        if (!$row = $this->store->findOneBy(['challenge_id', '==', $id])) {
            throw new EntityNotFound(sprintf('Trophy "%s" not found', $id));
        }

        return Trophy::fromMap($row);
    }

    public function add(Trophy $trophy): void
    {
        $this->store->insert($trophy->jsonSerialize());
    }
}
