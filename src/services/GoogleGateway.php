<?php

namespace Daftswag\Services;

use GuzzleHttp\Client;

class GoogleGateway
{
    const BASE_URI = 'https://www.googleapis.com/';
    private $client;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 2.0, 'base_uri' => static::BASE_URI]);
    }

    public function addUser($givenName, $familyName, $password, $primaryEmail)
    {
        $res = $this->client->post('/admin/directory/v1/users', [
            'name' => [
                'givenName' => $givenName,
                'familyName' => $familyName,
            ],
            'password' => $password,
            'primaryEmail' => $primaryEmail,
            'changePasswordAtNextLogin' => true,
        ]);
        return json_decode($res->getBody()->getContents(), true);
    }
}
