<?php

namespace App\Domain\Strava\Gear;

class Gear implements \JsonSerializable
{
    private function __construct(
        private array $data
    ) {
    }

    public static function create(array $data, \DateTimeImmutable $createdOn): self
    {
        $data['createdOn'] = $createdOn->getTimestamp();

        return new self($data);
    }

    public static function fromMap(array $data): self
    {
        return new self($data);
    }

    public function updateDistance(float $distance): void
    {
        $this->data['distance'] = $distance;
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
