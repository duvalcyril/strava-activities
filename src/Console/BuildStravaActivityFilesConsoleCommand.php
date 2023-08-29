<?php

namespace App\Console;

use App\Domain\Strava\BuildLatestStravaActivities\BuildLatestStravaActivities;
use App\Domain\Strava\BuildReadMe\BuildReadMe;
use App\Domain\Strava\BuildWeeklyDistanceChart\BuildWeeklyDistanceChart;
use App\Infrastructure\CQRS\CommandBus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:strava:build-files', description: 'Build Strava files')]
class BuildStravaActivityFilesConsoleCommand extends Command
{
    public function __construct(
        private readonly CommandBus $commandBus
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->commandBus->dispatch(new BuildLatestStravaActivities());
        $this->commandBus->dispatch(new BuildWeeklyDistanceChart());
        $this->commandBus->dispatch(new BuildReadMe());

        return Command::SUCCESS;
    }
}
