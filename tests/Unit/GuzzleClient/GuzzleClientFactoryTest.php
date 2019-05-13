<?php

namespace App\Tests\Unit\GuzzleClient;

use App\Services\GuzzleClient\GuzzleClientFactory;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\TestCase;

class GuzzleClientFactoryTest extends TestCase
{
    public function testCreateFactoryMethod()
    {
        $obj = new GuzzleClientFactory();
        $this->assertInstanceOf(ClientInterface::class, $obj::factoryMethod());
    }
}
