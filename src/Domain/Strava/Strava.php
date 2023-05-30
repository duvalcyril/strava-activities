<?php

namespace App\Domain\Strava;

use App\Infrastructure\Serialization\Json;
use GuzzleHttp\Client;

final class Strava
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    private function request(
        string $path,
        string $method = 'GET',
        array $options = []): string
    {
        $options = array_merge([
            'base_uri' => 'https://www.strava.com/',
        ], $options);
        $response = $this->client->request($method, $path, $options);

        return $response->getBody()->getContents();
    }

    public function getActivities(): array
    {
    }

    public function getPublicProfile(int $athleteId): array
    {
        $contents = $this->request('athletes/'.$athleteId);
        if (!preg_match('/data-react-class=\'AthleteProfileApp\'[\s]+data-react-props=\'(?<profile>.*?)\'/', $contents, $matches)) {
            throw new \RuntimeException('Could not fetch Strava profile');
        }

        return Json::decode(html_entity_decode($matches['profile']));
    }

    public function getChallenges(int $athleteId): array
    {
        $contents = $this->request('athletes/'.$athleteId);
        if (!preg_match('/data-react-class=\'AthleteProfileApp\'[\s]+data-react-props=\'(?<profile>.*?)\'/', $contents, $matches)) {
            throw new \RuntimeException('Could not fetch Strava profile');
        }

        return Json::decode(html_entity_decode($matches['profile']['trophies'] ?? '[]'));
    }

    public function downloadImage($uri): string
    {
        $response = $this->client->request('GET', $uri);

        return $response->getBody()->getContents();
    }
}
