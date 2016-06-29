<?php

namespace Daftswag\Services;

use GuzzleHttp\Client;

class SlackGateway
{
    private $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function addUserToTeam($team, $email)
    {
        $res = (new Client(['timeout' => 2.0, 'base_uri' => "https://{$team}.slack.com/"]))
            ->post('/api/users.admin.invite?' . http_build_query([
                    'email' => $email,
                    'token' => $this->token,
                    'set_active' => true
                ]));
        return json_decode($res->getBody()->getContents(), true);
    }
}
