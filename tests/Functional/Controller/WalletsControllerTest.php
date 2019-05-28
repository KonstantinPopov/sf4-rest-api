<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WalletsControllerTest extends WebTestCase
{

    public function testListActionAuthenticationFailed()
    {
        $client = static::createClient();
        $client->request('GET','http://127.0.0.1:8080/api/wallets');

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"message":"Authentication Required"}', $client->getResponse()->getContent());
    }


    public function testListAction()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            'http://127.0.0.1:8080/api/wallets',
            [],
            [],
            [
                'HTTP_X_AUTH_TOKEN' => '1234567890',
            ]
        );
    }
}
