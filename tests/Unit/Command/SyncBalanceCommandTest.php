<?php

namespace App\Tests\Unit\Command;

use App\Command\SyncBalanceCommand;
use App\Entity\Wallet;
use App\Repository\WalletRepository;
use App\Services\Wallet\WalletService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SyncBalanceCommandTest extends KernelTestCase
{
    public function testSyncBalance()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $logger = $this->createMock(LoggerInterface::class);
        $walletService = $this->createMock(WalletService::class);
        $walletService
            ->expects($this->once())
            ->method('syncBalance');

        $walletRepository = $this->createMock(WalletRepository::class);

        $application = new Application($kernel);
        $application->add(new SyncBalanceCommand($logger, $walletService, $walletRepository));

        $command = $application->find('app:sync-balance');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(),]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('balance was synced', $output);
    }

    public function testSyncBalanceByWallet()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $wallet = new Wallet();
        $wallet->setAddress('test-address');

        $logger = $this->createMock(LoggerInterface::class);
        $walletService = $this->createMock(WalletService::class);
        $walletService
            ->expects($this->once())
            ->method('syncBalance')
            ->with($wallet);

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['address' => 'test-address'])
            ->willReturn($wallet);

        $application = new Application($kernel);
        $application->add(new SyncBalanceCommand($logger, $walletService, $walletRepository));

        $command = $application->find('app:sync-balance');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), 'wallet' => 'test-address',]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('balance was synced', $output);
    }

    public function testSyncBalanceByWalletNotFound()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $wallet = new Wallet();
        $wallet->setAddress('test-address');

        $logger = $this->createMock(LoggerInterface::class);
        $walletService = $this->createMock(WalletService::class);
        $walletService
            ->expects($this->never())
            ->method('syncBalance');

        $walletRepository = $this->createMock(WalletRepository::class);

        $application = new Application($kernel);
        $application->add(new SyncBalanceCommand($logger, $walletService, $walletRepository));

        $command = $application->find('app:sync-balance');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), 'wallet' => 'test-address',]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Nothing synced', $output);
    }

    public function testSyncBalanceWithExceptionSync()
    {
        $exception = new \Exception('test error');
        $kernel = static::createKernel();
        $kernel->boot();

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('Balance not synced');
        $walletService = $this->createMock(WalletService::class);
        $walletService
            ->expects($this->once())
            ->method('syncBalance')
            ->willThrowException($exception);

        $walletRepository = $this->createMock(WalletRepository::class);
        $application = new Application($kernel);
        $application->add(new SyncBalanceCommand($logger, $walletService, $walletRepository));

        $command = $application->find('app:sync-balance');
        $commandTester = new CommandTester($command);

        $this->expectExceptionObject($exception);
        $commandTester->execute(['command' => $command->getName(),]);

        $this->assertTrue(false); // won't be check
    }
}
