<?php

namespace App\Domain\Strava\ImportChallenges;

use App\Domain\Strava\Challenge\Challenge;
use App\Domain\Strava\Challenge\StravaChallengeRepository;
use App\Domain\Strava\Strava;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Exception\EntityNotFound;
use Lcobucci\Clock\Clock;
use League\Flysystem\Filesystem;
use Ramsey\Uuid\Rfc4122\UuidV5;

#[AsCommandHandler]
class ImportChallengesCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly Strava $strava,
        private readonly StravaChallengeRepository $stravaChallengeRepository,
        private readonly Filesystem $filesystem,
        private readonly Clock $clock
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof ImportChallenges);

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
                sleep(1); // Make sure timestamp is increased by at least one.
            }
        }
    }
}
