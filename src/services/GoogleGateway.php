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
        $this->client->setScopes($this->getScopes());
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

    public function addUser(array $user)
    {
        $user = new Google_Service_Directory_User($user);
        return $this->service->users->insert($user);
    }

    private function getScopes()
    {
        return [
            Google_Service_Directory::ADMIN_DIRECTORY_CUSTOMER,
            Google_Service_Directory::ADMIN_DIRECTORY_CUSTOMER_READONLY,
            Google_Service_Directory::ADMIN_DIRECTORY_DEVICE_CHROMEOS,
            Google_Service_Directory::ADMIN_DIRECTORY_DEVICE_CHROMEOS_READONLY,
            Google_Service_Directory::ADMIN_DIRECTORY_DEVICE_MOBILE,
            Google_Service_Directory::ADMIN_DIRECTORY_DEVICE_MOBILE_ACTION,
            Google_Service_Directory::ADMIN_DIRECTORY_DEVICE_MOBILE_READONLY,
            Google_Service_Directory::ADMIN_DIRECTORY_DOMAIN,
            Google_Service_Directory::ADMIN_DIRECTORY_DOMAIN_READONLY,
            Google_Service_Directory::ADMIN_DIRECTORY_GROUP,
            Google_Service_Directory::ADMIN_DIRECTORY_GROUP_MEMBER,
            Google_Service_Directory::ADMIN_DIRECTORY_GROUP_MEMBER_READONLY,
            Google_Service_Directory::ADMIN_DIRECTORY_GROUP_READONLY,
            Google_Service_Directory::ADMIN_DIRECTORY_NOTIFICATIONS,
            Google_Service_Directory::ADMIN_DIRECTORY_ORGUNIT,
            Google_Service_Directory::ADMIN_DIRECTORY_ORGUNIT_READONLY,
            Google_Service_Directory::ADMIN_DIRECTORY_RESOURCE_CALENDAR,
            Google_Service_Directory::ADMIN_DIRECTORY_RESOURCE_CALENDAR_READONLY,
            Google_Service_Directory::ADMIN_DIRECTORY_ROLEMANAGEMENT,
            Google_Service_Directory::ADMIN_DIRECTORY_ROLEMANAGEMENT_READONLY,
            Google_Service_Directory::ADMIN_DIRECTORY_USER,
            Google_Service_Directory::ADMIN_DIRECTORY_USER_ALIAS,
            Google_Service_Directory::ADMIN_DIRECTORY_USER_ALIAS_READONLY,
            Google_Service_Directory::ADMIN_DIRECTORY_USER_READONLY,
            Google_Service_Directory::ADMIN_DIRECTORY_USER_SECURITY,
            Google_Service_Directory::ADMIN_DIRECTORY_USERSCHEMA,
            Google_Service_Directory::ADMIN_DIRECTORY_USERSCHEMA_READONLY,
        ];
    }
}
