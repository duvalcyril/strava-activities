<?php

namespace App\Domain\Weather\OpenMeteo;

final readonly class Weather implements \JsonSerializable
{
    private function __construct(
        private array $data
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
