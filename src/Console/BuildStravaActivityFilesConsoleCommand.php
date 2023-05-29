<?php

namespace App\Console;

use App\Domain\ReadMe;
use App\Domain\Strava\StravaActivityRepository;
use App\Domain\Strava\StravaTrophyRepository;
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
        private readonly StravaTrophyRepository $stravaTrophyRepository,
        private readonly Environment $twig
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        file_put_contents(
            Settings::getAppRoot().'/build/strava-activities-latest.md',
            $this->twig->load('strava-activities.html.twig')->render([
                'activities' => $this->stravaActivityRepository->findAll(3),
            ])
        );

        $pathToReadMe = Settings::getAppRoot().'/README.md';
        $readme = ReadMe::fromPathToReadMe($pathToReadMe);

        $readme
            ->updateStravaActivities($this->twig->load('strava-activities.html.twig')->render([
                'activities' => $this->stravaActivityRepository->findAll(),
            ]))
        ->updateStravaChallenges($this->twig->load('strava-challenges.html.twig')->render([
            'trophies' => $this->stravaTrophyRepository->findAll(),
        ]));

        \Safe\file_put_contents($pathToReadMe, (string) $readme);

        return Command::SUCCESS;
    }
}
