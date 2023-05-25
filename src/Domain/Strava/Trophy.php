<?php

namespace App\Domain\Strava;

class Trophy
{
    private function __construct(
        private readonly array $data
    ) {
    }

    public static function fromMap(array $data): self
    {
        return new self($data);
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
