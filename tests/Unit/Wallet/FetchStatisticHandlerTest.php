<?php

namespace App\Tests\Unit\Wallet;

use App\Entity\BalanceLog;
use App\Entity\Currency;
use App\Entity\Wallet;
use App\Exception\AdapterNotFoundException;
use App\Services\Wallet\ApiAdapters\AdapterChain;
use App\Services\Wallet\ApiAdapters\AdapterInterface;
use App\Services\Wallet\FetchStatisticHandlers\FetchStatisticHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FetchStatisticHandlerTest extends TestCase
{
    /** @var AdapterChain|MockObject */
    private $adapterChainMock;

    /** @var LoggerInterface|MockObject */
    private $loggerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapterChainMock = $this->createMock(AdapterChain::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

    }

    public function testNewInstance()
    {
        $fetchStatisticHandler = new FetchStatisticHandler(
            $this->adapterChainMock,
            $this->loggerMock
        );

        $this->assertInstanceOf(FetchStatisticHandler::class, $fetchStatisticHandler);
    }

    public function getBalanceByWalletDataProvider()
    {
        $wallet = new Wallet();
        $currency = new Currency();
        $currency->setCode('BTC');
        $wallet->setCurrency($currency);
        $wallet->setAddress('test-address');
        $currency->setApiAdapterSlug(AdapterInterface::SLUG);

        return [
            [$wallet,]
        ];
    }

    /**
     * @dataProvider getBalanceByWalletDataProvider
     */
    public function testGetBalanceByWallet(Wallet $wallet)
    {
        $balance = new BalanceLog();
        $balance->setWallet($wallet);
        $balance->setBalance(11.22);

        $adapterMock = $this->createMock(AdapterInterface::class);
        $adapterMock
            ->expects($this->once())
            ->method('fetchBalance')
            ->with($wallet)
            ->willReturn(11.22);

        $adapterChainMock = $this->adapterChainMock;
        $adapterChainMock
            ->expects($this->once())
            ->method('getAdapter')
            ->with(AdapterInterface::SLUG)
            ->willReturn($adapterMock);

        $logger = $this->loggerMock;
        $logger
            ->expects($this->once())
            ->method('info')
            ->with(
                'Fetch statistic',
                [
                    'wallet' => 'test-address',
                    'api_adapter' => AdapterInterface::SLUG,
                ]
            );

        $fetchStatisticHandler = new FetchStatisticHandler($adapterChainMock, $logger);
        $balanceResult = $fetchStatisticHandler->getBalanceByWallet($wallet);

        $this->assertEquals($balanceResult, $balance);
    }

    /**
     * @dataProvider getBalanceByWalletDataProvider
     */
    public function testGetBalanceByWalletNotFoundAdapter(Wallet $wallet)
    {
        $this->expectException(AdapterNotFoundException::class);
        $this->expectExceptionMessage('Api adapter "adapter-interface" not supported');

        $fetchStatisticHandler = new FetchStatisticHandler($this->adapterChainMock, $this->loggerMock);
        $fetchStatisticHandler->getBalanceByWallet($wallet);

        $this->assertFalse(true);// won't be check
    }
}
