<?php

namespace App\Console;

use App\Domain\Strava\Activity;
use App\Domain\Strava\Strava;
use App\Domain\Strava\StravaActivityRepository;
use App\Infrastructure\Exception\EntityNotFound;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:strava:import-activity', description: 'Build site')]
class ImportStravaActivityConsoleCommand extends Command
{
    public function __construct(
        private readonly Strava $strava,
        private readonly StravaActivityRepository $stravaActivityRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $publicProfile = $this->strava->getPublicProfile(62214940);

        foreach (array_reverse($publicProfile['recentActivities']) ?? [] as $recentActivity) {
            try {
                $this->stravaActivityRepository->findOneBy($recentActivity['id']);
            } catch (EntityNotFound) {
                $this->stravaActivityRepository->add(Activity::fromMap($recentActivity));
            }
        }

        return Command::SUCCESS;
    }
}
