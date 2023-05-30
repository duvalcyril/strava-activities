<?php

namespace App\Console;

use App\Domain\Strava\Activity;
use App\Domain\Strava\Challenge;
use App\Domain\Strava\Strava;
use App\Domain\Strava\StravaActivityRepository;
use App\Domain\Strava\StravaChallengeRepository;
use App\Infrastructure\Exception\EntityNotFound;
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
                $activity = Activity::fromMap($recentActivity);

                foreach ($activity->getImages() as $image) {
                    if (!empty($image['defaultSrc'])) {
                        $imagePath = sprintf('files/activities/%s/%s.png', $activity->getId(), UuidV5::uuid1());
                        $this->filesystem->write(
                            $imagePath,
                            $this->strava->downloadImage($image['defaultSrc'])
                        );

                        $activity->addDefaultLocalImage($imagePath);
                    }

                    if (!empty($image['squareSrc'])) {
                        $imagePath = sprintf('files/activities/%s/%s.png', $activity->getId(), UuidV5::uuid1());
                        $this->filesystem->write(
                            $imagePath,
                            $this->strava->downloadImage($image['squareSrc'])
                        );

                        $activity->addSquareLocalImage($imagePath);
                    }
                }

                $this->stravaActivityRepository->add($activity);
            }
        }

        foreach (array_reverse($this->strava->getChallenges(62214940)) ?? [] as $trophyData) {
            try {
                $this->stravaChallengeRepository->findOneBy($trophyData['challenge_id']);
            } catch (EntityNotFound) {
                $trophy = Challenge::fromMap($trophyData);
                if ($url = $trophy->getLogoUrl()) {
                    $imagePath = sprintf('files/trophies/%s.png', UuidV5::uuid1());
                    $this->filesystem->write(
                        $imagePath,
                        $this->strava->downloadImage($url)
                    );

                    $trophy->updateLocalLogo($imagePath);
                }
                $this->stravaChallengeRepository->add($trophy);
            }
        }

        return Command::SUCCESS;
    }
}
