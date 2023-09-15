<?php

namespace App\Domain\Strava\Gear\ImportGear;

use App\Infrastructure\CQRS\ConsoleOutputAwareDomainCommand;
use App\Infrastructure\CQRS\DomainCommand;

class ImportGear extends DomainCommand
{
    use ConsoleOutputAwareDomainCommand;
}
