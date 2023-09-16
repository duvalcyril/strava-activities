<?php

namespace App\Domain\Strava\Challenge\ImportChallenges;

use App\Infrastructure\CQRS\ConsoleOutputAwareDomainCommand;
use App\Infrastructure\CQRS\DomainCommand;

class ImportChallenges extends DomainCommand
{
    use ConsoleOutputAwareDomainCommand;
}
