<?php

namespace App\Domain\Strava;

use Carbon\CarbonInterval;

class Activity implements \JsonSerializable
{
    private function __construct(
        private array $data
    ) {
    }

    public static function create(array $data): self
    {
        $data['start_date_timestamp'] = \DateTimeImmutable::createFromFormat(
            'Y-m-d\TH:i:s\Z',
            $data['start_date']
        )->getTimestamp();

        return new self($data);
    }

    public static function fromMap(array $data): self
    {
        return new self($data);
    }

    public function getId(): int
    {
        return (int) $this->data['id'];
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat(
            'Y-m-d\TH:i:s\Z',
            $this->data['start_date']
        );
    }

    public function getType(): ActivityType
    {
        return ActivityType::from($this->data['type']);
    }

    public function getName(): string
    {
        return $this->data['name'];
    }

    public function getDistance(): float
    {
        return round($this->data['distance'] / 1000);
    }

    public function getElevation(): int
    {
        return round($this->data['total_elevation_gain']);
    }

    public function getAveragePower(): ?int
    {
        if (isset($this->data['average_watts'])) {
            return round($this->data['average_watts']);
        }

        return null;
    }

    public function getAverageSpeedInKmPerH(): float
    {
        return round($this->data['average_speed'] * 3.6, 1);
    }

    public function getMovingTime(): string
    {
        $interval = CarbonInterval::seconds($this->data['moving_time'])->cascade();

        $movingTime = implode(':', array_filter(array_map(fn (int $value) => sprintf('%02d', $value), [
            $interval->minutes,
            $interval->seconds,
        ])));

        if ($hours = $interval->hours) {
            $movingTime = $hours.':'.$movingTime;
        }

        return ltrim($movingTime, '0');
    }

    public function getUrl(): string
    {
        return 'https://www.strava.com/activities/'.$this->data['id'];
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
