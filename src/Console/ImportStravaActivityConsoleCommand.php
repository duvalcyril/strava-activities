<?php

namespace App\Console;

use App\Domain\Strava\Activity;
use App\Domain\Strava\Strava;
use App\Domain\Strava\StravaActivityRepository;
use App\Domain\Strava\StravaTrophyRepository;
use App\Domain\Strava\Trophy;
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
        private readonly StravaTrophyRepository $stravaTrophyRepository
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

        foreach (array_reverse($publicProfile['trophies']) ?? [] as $trophy) {
            try {
                $this->stravaTrophyRepository->findOneBy($trophy['challenge_id']);
            } catch (EntityNotFound) {
                $this->stravaTrophyRepository->add(Trophy::fromMap($trophy));
            }
        }

        return Command::SUCCESS;
    }
}
