<?php

namespace App\Domain\Strava;

class Trophy
{
    private function __construct(
        private array $data
    ) {
    }

    public static function fromMap(array $data): self
    {
        return new self($data);
    }

    public function getLogoUrl(): ?string
    {
        return $this->data['logo_url'] ?? null;
    }

    public function getLocalLogoUrl(): ?string
    {
        return $this->data['localLogo'] ?? null;
    }

    public function updateLocalLogo(string $path): void
    {
        $this->data['localLogo'] = $path;
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
