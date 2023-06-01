<?php

namespace App\Domain\Strava;

enum ActivityType: string
{
    case RIDE = 'Ride';
    case VIRTUAL_RIDE = 'VirtualRide';

    public function getIcon(): string
    {
        return match ($this) {
            self::RIDE => 'activity-ride',
            self::VIRTUAL_RIDE => 'activity-virtual-ride',
        };
    }
}
