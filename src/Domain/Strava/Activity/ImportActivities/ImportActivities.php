<?php

namespace App\Domain\Strava\Activity\ImportActivities;

use App\Infrastructure\CQRS\ConsoleOutputAwareDomainCommand;
use App\Infrastructure\CQRS\DomainCommand;

class ImportActivities extends DomainCommand
{
    use ConsoleOutputAwareDomainCommand;
}
