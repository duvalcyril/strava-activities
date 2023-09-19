<?php

namespace App\Domain\Strava\Activity\BuildEddingtonChart;

use App\Domain\Strava\Activity\StravaActivityRepository;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Environment\Settings;
use App\Infrastructure\Serialization\Json;

#[AsCommandHandler]
final readonly class BuildEddingtonChartCommandHandler implements CommandHandler
{
    public function __construct(
        private StravaActivityRepository $stravaActivityRepository,
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildEddingtonChart);

        $eddington = Eddington::fromActivities($this->stravaActivityRepository->findAll());
        $longestDistanceInADay = $eddington->getLongestDistanceInADay();
        $timesCompletedData = $eddington->getTimesCompletedData();
        $eddingtonNumber = $eddington->getNumber();
        $yAxisMaxValue = ceil(max($timesCompletedData) / 30) * 30;

        \Safe\file_put_contents(
            Settings::getAppRoot().'/build/chart-activities-eddington.json',
            Json::encode([
                'backgroundColor' => '#ffffff',
                'animation' => false,
                'grid' => [
                    'left' => '3%',
                    'right' => '4%',
                    'bottom' => '3%',
                    'containLabel' => true,
                ],
                'legend' => [
                    'show' => true,
                ],
                'xAxis' => [
                    'data' => array_map(fn (int $distance) => $distance.'km', range(1, $longestDistanceInADay)),
                    'type' => 'category',
                    'axisTick' => [
                        'alignWithLabel' => true,
                    ],
                ],
                'yAxis' => [
                    [
                        'type' => 'value',
                        'splitLine' => [
                            'show' => true,
                        ],
                        'max' => $yAxisMaxValue,
                        'interval' => 30,
                    ],
                    [
                        'type' => 'value',
                        'splitLine' => [
                            'show' => false,
                        ],
                        'max' => $yAxisMaxValue,
                        'interval' => 30,
                    ],
                ],
                'series' => [
                    [
                        'name' => 'Times completed',
                        'yAxisIndex' => 0,
                        'type' => 'bar',
                        'label' => [
                            'show' => false,
                        ],
                        'showBackground' => false,
                        'itemStyle' => [
                            'color' => 'rgba(227, 73, 2, 0.3)',
                        ],
                        'markPoint' => [
                            'symbol' => 'pin',
                            'symbolOffset' => [
                                0,
                                -5,
                            ],
                            'itemStyle' => [
                                'color' => 'rgba(227, 73, 2, 0.8)',
                            ],
                            'data' => [
                                [
                                    'value' => $eddingtonNumber,
                                    'coord' => [
                                        $eddingtonNumber - 1,
                                        $eddingtonNumber - 1,
                                    ],
                                ],
                            ],
                        ],
                        'data' => array_map(fn (int $timesCompleted) => $timesCompleted === $eddingtonNumber ?
                            [
                              'value' => $timesCompleted,
                              'itemStyle' => [
                                'color' => 'rgba(227, 73, 2, 0.8)',
                                ],
                            ] : $timesCompleted, $timesCompletedData),
                    ],
                    [
                        'name' => 'Eddington',
                        'yAxisIndex' => 1,
                        'zlevel' => 1,
                        'type' => 'line',
                        'smooth' => false,
                        'showSymbol' => false,
                        'label' => [
                            'show' => false,
                        ],
                        'showBackground' => false,
                        'itemStyle' => [
                            'color' => '#E34902',
                        ],
                        'data' => range(1, $longestDistanceInADay),
                    ],
                ],
            ], JSON_PRETTY_PRINT),
        );
    }
}
