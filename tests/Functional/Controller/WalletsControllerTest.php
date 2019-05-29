<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WalletsControllerTest extends WebTestCase
{

    public function testAuthenticationFailed()
    {
        $client = static::createClient();

        $client->request('GET','http://127.0.0.1:8080/api/wallets');
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"message":"Authentication Required"}', $client->getResponse()->getContent());

        $client->request('GET','http://127.0.0.1:8080/api/wallet/385cR5DM96n1HvBDMzLHPYcw89fZAXULJP');
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
                'HTTP_ACCEPT' => 'application/json'
            ]
        );

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $body = json_decode($client->getResponse()->getContent(), true);
        $element = current($body);
        $this->assertArrayHasKey('currency', $element);
        $this->assertArrayHasKey('balance', $element);
        $this->assertArrayHasKey('balance_changed_at', $element);
        $this->assertArrayHasKey('address', $element);
    }

    public function testWatchBalanceListAction()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            'http://127.0.0.1:8080/api/wallet/385cR5DM96n1HvBDMzLHPYcw89fZAXULJP',
            [],
            [],
            [
                'HTTP_X_AUTH_TOKEN' => '1234567890',
                'HTTP_ACCEPT' => 'application/json'
            ]
        );

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('currency', $body);
        $this->assertArrayHasKey('balance', $body);
        $this->assertArrayHasKey('balance_changed_at', $body);
        $this->assertArrayHasKey('address', $body);
        $this->assertArrayHasKey('balance_log', $body);
    }

    public function testWatchBalanceListActionNotExistWallet()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            'http://127.0.0.1:8080/api/wallet/no_exist',
            [],
            [],
            [
                'HTTP_X_AUTH_TOKEN' => '1234567890',
                'HTTP_ACCEPT' => 'application/json'
            ]
        );

        $this->assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
    }

    public function testAddWalletAction()
    {
        $client = static::createClient();
        $client->request(
            'POST',
            'http://127.0.0.1:8080/api/wallets',
            [
                'address' => '3E4ozZLgUdYdQoiJhS6NPK1XVSf8dKix2U',
                'currency' => 'btc',
            ],
            [],
            [
                'HTTP_X_AUTH_TOKEN' => '1234567890',
                'HTTP_ACCEPT' => 'application/json'
            ]
        );

        $this->assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{ "status": "success"}', $client->getResponse()->getContent());

        $client->request(
            'GET',
            'http://127.0.0.1:8080/api/wallet/3E4ozZLgUdYdQoiJhS6NPK1XVSf8dKix2U',
            [],
            [],
            [
                'HTTP_X_AUTH_TOKEN' => '1234567890',
                'HTTP_ACCEPT' => 'application/json'
            ]
        );

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('address', $body);
        $this->assertEquals('3E4ozZLgUdYdQoiJhS6NPK1XVSf8dKix2U', $body['address']);
    }
}
