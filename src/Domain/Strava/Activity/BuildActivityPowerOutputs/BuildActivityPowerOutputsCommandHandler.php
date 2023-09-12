<?php

namespace App\Domain\Strava\Activity\BuildActivityPowerOutputs;

use App\Domain\Strava\Activity\StravaActivityRepository;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Environment\Settings;
use Twig\Environment;

#[AsCommandHandler]
final readonly class BuildActivityPowerOutputsCommandHandler implements CommandHandler
{
    public function __construct(
        private StravaActivityRepository $stravaActivityRepository,
        private Environment $twig,
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildActivityPowerOutputs);

        \Safe\file_put_contents(
            Settings::getAppRoot().'/build/strava-activities-power-outputs.md',
            $this->twig->load('strava-activities-power-outputs.html.twig')->render([
                'activities' => $this->stravaActivityRepository->findWithPower(),
            ])
        );
    }
}
