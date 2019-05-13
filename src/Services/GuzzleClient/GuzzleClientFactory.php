<?php

namespace App\Services\GuzzleClient;

use GuzzleHttp\Client;

class GuzzleClientFactory
{
    public static function factoryMethod(): Client
    {
        return new Client();
    }
}
