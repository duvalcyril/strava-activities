<?php

use App\Domain\Strava\StravaActivityRepository;
use App\Domain\Strava\StravaTrophyRepository as StravaTrophyRepositoryAlias;
use App\Infrastructure\Console\ConsoleCommandContainer;
use App\Infrastructure\Environment\Environment;
use App\Infrastructure\Environment\Settings;
use App\Infrastructure\Twig\TwigBuilder;
use Dotenv\Dotenv;
use Lcobucci\Clock\Clock;
use Lcobucci\Clock\SystemClock;
use SleekDB\Store;
use Symfony\Component\Console\Application;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;

$appRoot = Settings::getAppRoot();

$dotenv = Dotenv::createImmutable($appRoot);
$dotenv->load();

return [
    // Clock.
    Clock::class => DI\factory([SystemClock::class, 'fromSystemTimezone']),
    // Twig Environment.
    FilesystemLoader::class => DI\create(FilesystemLoader::class)->constructor($appRoot.'/templates'),
    TwigEnvironment::class => DI\factory([TwigBuilder::class, 'build']),
    // Console command application.
    Application::class => function (ConsoleCommandContainer $consoleCommandContainer) {
        $application = new Application();
        foreach ($consoleCommandContainer->getCommands() as $command) {
            $application->add($command);
        }

        return $application;
    },
    // Environment.
    Environment::class => fn () => Environment::from($_ENV['ENVIRONMENT']),
    // Settings.
    Settings::class => DI\factory([Settings::class, 'load']),
    StravaActivityRepository::class => DI\autowire()->constructorParameter('store', new Store('activities', $appRoot.'/database', [
        'auto_cache' => false,
        'timeout' => false,
    ])),
    StravaTrophyRepositoryAlias::class => DI\autowire()->constructorParameter('store', new Store('trophies', $appRoot.'/database', [
        'auto_cache' => false,
        'timeout' => false,
    ])),
];
