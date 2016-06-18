<?php

namespace Daftswag\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;

class GitHubGateway
{
    const ENGINEERS_GROUP_ID = 894740;
    const BASE_URI = 'https://api.github.com/';
    private $client;
    private $username;
    private $token;

    public function __construct($username, $token)
    {
        if (!$token) {
            throw new InvalidArgumentException("Pivotal API token is required.");
        }
        $this->username = $username;
        $this->token = $token;
        $this->client = new Client(['timeout' => 2.0, 'base_uri' => static::BASE_URI, 'auth' => [$this->username, $this->token]]);
    }

    public function addUserToTeam($team, $username)
    {
        $res = $this->request('/teams/' . static::ENGINEERS_GROUP_ID . "/memberships/{$username}", 'PUT');
        return json_decode($res->getBody()->getContents(), true);
    }

    private function request($uri, $method = 'GET', array $options = [])
    {
        $options = array_merge_recursive($options, [
            'headers' => ['Accept' => 'application/vnd.github.v3+json'],
        ]);
        return $this->client->request($method, $uri, $options);

    }
}
