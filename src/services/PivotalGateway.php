<?php

namespace Daftswag\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;

class PivotalGateway
{
    const BASE_URI = 'https://www.pivotaltracker.com/';
    private $client;
    private $project;
    private $token;

    public function __construct($project, $token)
    {
        if (!$project) {
            throw new InvalidArgumentException("Pivotal project id is required.");
        }
        if (!$token) {
            throw new InvalidArgumentException("Pivotal API token is required.");
        }
        $this->client = new Client(['timeout' => 2.0, 'base_uri' => static::BASE_URI, ['headers' => ['X-TrackerToken' => $token]]]);
        $this->project = $project;
        $this->token = $token;
    }

    public function describeStories(array $storyIds)
    {
        $url = "services/v5/projects/{$this->project}/stories?filter=id:" . implode(',', $storyIds);
        return json_decode($this->request($url)->getBody()->getContents(), true);
    }

    private function request($uri, $method = 'GET', array $options = [])
    {
        $options = array_merge_recursive($options, [
            'headers' => ['X-TrackerToken' => $this->token],
        ]);
        return $this->client->request($method, $uri, $options);

    }
}
