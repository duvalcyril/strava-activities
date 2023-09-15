<?php

namespace App\Domain\Strava\Activity;

use App\Infrastructure\ValueObject\Geography\Latitude;
use App\Infrastructure\ValueObject\Geography\Longitude;
use App\Infrastructure\ValueObject\Weight;
use Carbon\CarbonInterval;

class Activity implements \JsonSerializable
{
    public const DATE_TIME_FORMAT = 'Y-m-d\TH:i:s\Z';
    private ?string $gearName;
    private array $bestAveragePowerForTimeIntervals = [];

    private function __construct(
        private array $data
    ) {
        $this->gearName = null;
    }

    public static function create(array $data): self
    {
        $data['start_date_timestamp'] = \DateTimeImmutable::createFromFormat(
            self::DATE_TIME_FORMAT,
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
            self::DATE_TIME_FORMAT,
            $this->data['start_date']
        );
    }

    public function getType(): ActivityType
    {
        return ActivityType::from($this->data['type']);
    }

    public function getLatitude(): ?Latitude
    {
        return Latitude::fromOptionalString($this->data['start_latlng'][0] ?? null);
    }

    public function getLongitude(): ?Longitude
    {
        return Longitude::fromOptionalString($this->data['start_latlng'][1] ?? null);
    }

    public function getGearId(): ?string
    {
        return $this->data['gear_id'] ?? null;
    }

    public function getGearName(): ?string
    {
        return $this->gearName;
    }

    public function enrichWithGearName(string $gearName): void
    {
        $this->gearName = $gearName;
    }

    public function updateWeather(array $weather): void
    {
        $this->data['weather'] = $weather;
    }

    public function getLocalImagePaths(): ?array
    {
        return $this->data['localImagePaths'] ?? null;
    }

    public function getTotalImageCount(): int
    {
        return $this->data['total_photo_count'] ?? 0;
    }

    public function updateLocalImagePaths(array $localImagePaths): void
    {
        $this->data['localImagePaths'] = $localImagePaths;
    }

    public function getName(): string
    {
        return trim(str_replace('Zwift - ', '', $this->data['name']));
    }

    public function getDistance(): float
    {
        return round($this->data['distance'] / 1000);
    }

    public function getElevation(): int
    {
        return round($this->data['total_elevation_gain']);
    }

    public function getCalories(): int
    {
        return $this->data['calories'] ?? 0;
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

    public function getAverageHeartRate(): ?int
    {
        if (isset($this->data['average_heartrate'])) {
            return round($this->data['average_heartrate']);
        }

        return null;
    }

    public function getMovingTime(): int
    {
        return $this->data['moving_time'];
    }

    public function getMovingTimeFormatted(): string
    {
        $interval = CarbonInterval::seconds($this->getMovingTime())->cascade();

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

    public function getBestAveragePowerForTimeInterval(int $timeIntervalInSeconds): ?int
    {
        if (array_key_exists($timeIntervalInSeconds, $this->bestAveragePowerForTimeIntervals)) {
            return $this->bestAveragePowerForTimeIntervals[$timeIntervalInSeconds];
        }

        if (!$bestSequence = $this->getBestSequence($timeIntervalInSeconds, StreamType::WATTS)) {
            $this->bestAveragePowerForTimeIntervals[$timeIntervalInSeconds] = null;

            return null;
        }

        $this->bestAveragePowerForTimeIntervals[$timeIntervalInSeconds] = round(array_sum($bestSequence) / $timeIntervalInSeconds);

        return $this->bestAveragePowerForTimeIntervals[$timeIntervalInSeconds];
    }

    public function getBestRelativeAveragePowerForTimeInterval(int $timeIntervalInSeconds): ?float
    {
        if ($averagePower = $this->getBestAveragePowerForTimeInterval($timeIntervalInSeconds)) {
            return round($averagePower / $this->getAthleteWeight()->getFloat(), 2);
        }

        return null;
    }

    public function getBestSequence(int $sequenceLength, StreamType $streamType): array
    {
        $best = 0;
        $bestSequence = [];

        $sequence = $this->data['streams'][$streamType->value][0]['data'] ?? [];

        if (count($sequence) < $sequenceLength) {
            return [];
        }

        for ($i = 0; $i < count($sequence) - $sequenceLength; ++$i) {
            $copySequence = $sequence;
            $sequenceToCheck = array_slice($copySequence, $i, $sequenceLength);
            $total = array_sum($sequenceToCheck);

            if ($total > $best) {
                $best = $total;
                $bestSequence = $sequenceToCheck;
            }
        }

        return $bestSequence;
    }

    public function getIntensity(): ?int
    {
        // ((durationInSeconds * avgHeartRate) / (FTP * 3600)) * 100
        if (!$this->getAverageHeartRate()) {
            return null;
        }

        return round(($this->getMovingTime() * $this->getAverageHeartRate()) / (240 * 3600) * 100);
    }

    public function getAthleteWeight(): Weight
    {
        return Weight::fromKilograms($this->data['athlete_weight']);
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
