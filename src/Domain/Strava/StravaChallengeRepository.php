<?php

namespace App\Domain\Strava;

use App\Infrastructure\Exception\EntityNotFound;
use SleekDB\Store;

class StravaChallengeRepository
{
    public function __construct(
        private readonly Store $store
    ) {
    }

    /**
     * @return \App\Domain\Strava\Challenge[]
     */
    public function findAll(): array
    {
        return array_map(
            fn (array $row) => Challenge::fromMap($row),
            $this->store->findAll(['_id' => 'desc'])
        );
    }

    public function findOneBy(int $id): Challenge
    {
        if (!$row = $this->store->findOneBy(['challenge_id', '==', $id])) {
            throw new EntityNotFound(sprintf('Trophy "%s" not found', $id));
        }

        return Challenge::fromMap($row);
    }

    public function add(Challenge $trophy): void
    {
        $this->store->insert($trophy->jsonSerialize());
    }
}
