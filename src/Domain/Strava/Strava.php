<?php

namespace App\Domain\Strava;

use App\Infrastructure\Serialization\Json;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

final class Strava
{
    public function __construct(
        private readonly Client $client,
        private readonly StravaClientId $stravaClientId,
        private readonly StravaClientSecret $stravaClientSecret,
        private readonly StravaRefreshToken $stravaRefreshToken,
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

    private function getAccessToken(): string
    {
        $response = $this->request('oauth/token', 'POST', [
            RequestOptions::FORM_PARAMS => [
                'client_id' => (string) $this->stravaClientId,
                'client_secret' => (string) $this->stravaClientSecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => (string) $this->stravaRefreshToken,
            ],
        ]);

        return Json::decode($response)['access_token'] ?? throw new \RuntimeException('Could not fetch Strava accessToken');
    }

    public function getActivities(): array
    {
        return Json::decode($this->request('api/v3/athlete/activities', 'GET', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$this->getAccessToken(),
            ],
            RequestOptions::QUERY => [
                'per_page' => 100,
            ],
        ]));
    }

    public function getActivity(int $id): array
    {
        return Json::decode($this->request('api/v3/activities/'.$id, 'GET', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$this->getAccessToken(),
            ],
        ]));
    }

    public function getActivityPhotos(int $activityId): array
    {
        return Json::decode($this->request('api/v3/activities/'.$activityId.'/photos', 'GET', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$this->getAccessToken(),
            ],
            RequestOptions::QUERY => [
                'photo_sources' => true,
                'size' => 1980,
            ],
        ]));
    }

    public function getGear(string $id): array
    {
        return Json::decode($this->request('api/v3/gear/'.$id, 'GET', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$this->getAccessToken(),
            ],
        ]));
    }

    public function getChallenges(int $athleteId): array
    {
        $contents = $this->request('athletes/'.$athleteId);
        if (!preg_match('/data-react-class=\'AthleteProfileApp\'[\s]+data-react-props=\'(?<profile>.*?)\'/', $contents, $matches)) {
            throw new \RuntimeException('Could not fetch Strava profile');
        }

        return Json::decode(html_entity_decode($matches['profile'] ?? '[]'))['trophies'] ?? [];
    }

    public function downloadImage($uri): string
    {
        $response = $this->client->request('GET', $uri);

        return $response->getBody()->getContents();
    }
}
