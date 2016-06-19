<?php

namespace Daftswag\Services;

use Google_Client;
use Google_Service_Directory;
use Google_Service_Directory_User;

class GoogleGateway
{
    const BASE_URI = 'https://www.googleapis.com/';
    private $client;
    private $service;

    public function __construct($clientSecretFilePath)
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName('daftlabs');
        $this->client->setScopes(Google_Service_Directory::ADMIN_DIRECTORY_USER);
        $this->client->setAuthConfigFile($clientSecretFilePath);
        $this->client->setAccessType('offline');
        $this->service = new Google_Service_Directory($this->client);
    }

    public function generateToken($authCode)
    {
        return $this->client->authenticate($authCode);
    }

    public function setToken($token)
    {
        $this->client->setAccessToken($token);
        return $this;
    }

    public function createAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function addUser($givenName, $familyName, $password, $primaryEmail)
    {
        $user = new Google_Service_Directory_User([
            'name' => [
                'givenName' => $givenName,
                'familyName' => $familyName,
            ],
            'primaryEmail' => $primaryEmail,
            'password' => $password
        ]);
        return $this->service->users->insert($user);
    }
}
