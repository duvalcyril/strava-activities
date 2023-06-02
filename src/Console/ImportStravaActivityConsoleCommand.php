<?php

namespace App\Console;

use App\Domain\Strava\Activity\Activity;
use App\Domain\Strava\Activity\ActivityType;
use App\Domain\Strava\Activity\StravaActivityRepository;
use App\Domain\Strava\Challenge\Challenge;
use App\Domain\Strava\Challenge\StravaChallengeRepository;
use App\Domain\Strava\Strava;
use App\Infrastructure\Exception\EntityNotFound;
use Lcobucci\Clock\Clock;
use League\Flysystem\Filesystem;
use Ramsey\Uuid\Rfc4122\UuidV5;
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
        private readonly StravaChallengeRepository $stravaChallengeRepository,
        private readonly Filesystem $filesystem,
        private readonly Clock $clock
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->strava->getActivities() ?? [] as $stravaActivity) {
            if (!ActivityType::tryFrom($stravaActivity['type'])) {
                continue;
            }

            try {
                $this->stravaActivityRepository->findOneBy($stravaActivity['id']);
            } catch (EntityNotFound) {
                $activity = Activity::create($this->strava->getActivity($stravaActivity['id']));
                $this->stravaActivityRepository->add($activity);
            }
        }

        foreach ($this->strava->getChallenges(62214940) ?? [] as $challengeData) {
            try {
                $this->stravaChallengeRepository->findOneBy($challengeData['challenge_id']);
            } catch (EntityNotFound) {
                $challenge = Challenge::create(
                    $challengeData,
                    $this->clock->now()
                );
                if ($url = $challenge->getLogoUrl()) {
                    $imagePath = sprintf('files/challenges/%s.png', UuidV5::uuid1());
                    $this->filesystem->write(
                        $imagePath,
                        $this->strava->downloadImage($url)
                    );

                    $challenge->updateLocalLogo($imagePath);
                }
                $this->stravaChallengeRepository->add($challenge);
                sleep(1); // Make sure timestamp is increased by at leas one.
            }
        }

        return Command::SUCCESS;
    }
}
