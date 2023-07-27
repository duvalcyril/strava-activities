<?php

namespace App\Domain\Strava\Activity;

enum StreamType: string
{
    case WATTS = 'watts';
    case DISTANCE = 'distance';
}
