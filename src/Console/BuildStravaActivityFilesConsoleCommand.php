<?php

namespace App\Console;

use App\Domain\ReadMe;
use App\Domain\Strava\Activity\ActivityTotals;
use App\Domain\Strava\Activity\StravaActivityRepository;
use App\Domain\Strava\Challenge\StravaChallengeRepository;
use App\Infrastructure\Environment\Settings;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

#[AsCommand(name: 'app:strava:build-files', description: 'Build Strava files')]
class BuildStravaActivityFilesConsoleCommand extends Command
{
    public function __construct(
        private readonly StravaActivityRepository $stravaActivityRepository,
        private readonly StravaChallengeRepository $stravaChallengeRepository,
        private readonly Environment $twig
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        file_put_contents(
            Settings::getAppRoot().'/build/strava-activities-latest.md',
            $this->twig->load('strava-activities.html.twig')->render([
                'activities' => $this->stravaActivityRepository->findAll(5),
            ])
        );

        $pathToReadMe = Settings::getAppRoot().'/README.md';
        $readme = ReadMe::fromPathToReadMe($pathToReadMe);

        $allActivities = $this->stravaActivityRepository->findAll();

        $readme
            ->updateStravaTotals($this->twig->load('strava-totals.html.twig')->render([
                'totals' => ActivityTotals::fromActivities($allActivities),
            ]))
            ->updateStravaActivities($this->twig->load('strava-activities.html.twig')->render([
                'activities' => $allActivities,
            ]))
            ->updateStravaChallenges($this->twig->load('strava-challenges.html.twig')->render([
                'challenges' => $this->stravaChallengeRepository->findAll(),
            ]));

        \Safe\file_put_contents($pathToReadMe, (string) $readme);

        return Command::SUCCESS;
    }
}
