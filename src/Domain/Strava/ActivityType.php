<?php

namespace App\Domain\Strava;

enum ActivityType: string
{
    case VIRTUAL_RIDE = 'VirtualRide';
    case RIDE = 'Ride';
}
