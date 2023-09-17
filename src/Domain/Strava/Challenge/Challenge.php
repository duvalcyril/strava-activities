<?php

namespace App\Domain\Strava\Challenge;

use App\Infrastructure\ValueObject\Time\SerializableDateTime;

class Challenge
{
    private function __construct(
        private array $data
    ) {
    }

    public static function fromMap(array $data): self
    {
        return new self($data);
    }

    public static function create(array $data, SerializableDateTime $createdOn): self
    {
        $data['createdOn'] = $createdOn->getTimestamp();

        return new self($data);
    }

    public function getName(): string
    {
        return $this->data['name'];
    }

    public function getLogoUrl(): ?string
    {
        return $this->data['logo_url'] ?? null;
    }

    public function getLocalLogoUrl(): ?string
    {
        return $this->data['localLogo'] ?? null;
    }

    public function getUrl(): string
    {
        return 'https://www.strava.com/challenges/'.$this->data['url'];
    }

    public function updateLocalLogo(string $path): void
    {
        $this->data['localLogo'] = $path;
    }

    public function getCreatedOn(): SerializableDateTime
    {
        return SerializableDateTime::fromTimestamp($this->data['createdOn']);
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
