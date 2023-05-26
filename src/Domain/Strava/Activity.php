<?php

namespace App\Domain\Strava;

class Activity implements \JsonSerializable
{
    private function __construct(
        private array $data
    ) {
    }

    public static function fromMap(array $data): self
    {
        return new self($data);
    }

    public function getId(): int
    {
        return (int) $this->data['id'];
    }

    public function getImages(): array
    {
        return $this->data['images'] ?? [];
    }

    public function addDefaultLocalImage(string $image): void
    {
        $this->data['localDefaultImages'][] = $image;
    }

    public function addSquareLocalImage(string $image): void
    {
        $this->data['localSquareImages'][] = $image;
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
