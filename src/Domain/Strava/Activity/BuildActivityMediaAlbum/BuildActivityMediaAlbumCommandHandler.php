<?php

namespace App\Domain\Strava\Activity\BuildActivityMediaAlbum;

use App\Domain\Strava\Activity\StravaActivityRepository;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Environment\Settings;
use Twig\Environment;

#[AsCommandHandler]
final readonly class BuildActivityMediaAlbumCommandHandler implements CommandHandler
{
    public function __construct(
        private StravaActivityRepository $stravaActivityRepository,
        private Environment $twig,
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildActivityMediaAlbum);

        \Safe\file_put_contents(
            Settings::getAppRoot().'/build/strava-activities-album.md',
            $this->twig->load('strava-activities-album.html.twig')->render([
                'activities' => $this->stravaActivityRepository->findWithImages(),
            ])
        );
    }
}
