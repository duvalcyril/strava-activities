<?php

namespace App\Infrastructure\ValueObject\Time;

class SerializableDateTime extends \DateTimeImmutable implements \JsonSerializable, \Stringable
{
    public static function createFromFormat(string $format, string $datetime, $timezone = null): self
    {
        return self::fromString(
            parent::createFromFormat($format, $datetime, $timezone)->format('Y-m-d H:i:s')
        );
    }

    public static function fromString(string $string): self
    {
        return new static($string);
    }

    public static function fromTimestamp(int $unixTimestamp): self
    {
        return (new self())->setTimestamp($unixTimestamp);
    }

    public static function fromDateTimeImmutable(\DateTimeImmutable $date): self
    {
        return self::fromString($date->format('Y-m-d H:i:s'));
    }

    public function __toString(): string
    {
        return $this->format('Y-m-d H:i:s');
    }

    public function jsonSerialize(): string
    {
        return $this->format('Y-m-d H:i:s');
    }

    public function getMinutesSinceStartOfDay(): int
    {
        return ($this->getHourWithoutLeadingZero() * 60) + $this->getMinutesWithoutLeadingZero();
    }

    public function getHourWithoutLeadingZero(): int
    {
        return (int) $this->format('G');
    }

    public function getMinutesWithoutLeadingZero(): int
    {
        return intval($this->format('i'));
    }

    public function isAfterOrOn(SerializableDateTime $that): bool
    {
        return $this >= $that;
    }

    public function isBeforeOrOn(SerializableDateTime $that): bool
    {
        return $this <= $that;
    }

    public function isBefore(SerializableDateTime $that): bool
    {
        return !$this->isAfterOrOn($that);
    }
}
