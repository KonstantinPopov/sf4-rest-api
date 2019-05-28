<?php

namespace App\Tests\Unit\Services\Wallet;

use App\Entity\BalanceLog;
use App\Entity\Currency;
use App\Entity\User;
use App\Entity\Wallet;
use App\Exception\SyncBalanceException;
use App\Repository\CurrencyRepository;
use App\Repository\WalletRepository;
use App\Services\Wallet\FetchStatisticHandlers\FetchStatisticHandlerInterface;
use App\Services\Wallet\WalletService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WalletServiceTest extends TestCase
{
    /** @var EntityManagerInterface|MockObject */
    private $entityMangerMock;

    /** @var ValidatorInterface|MockObject */
    private $validatorMock;

    /** @var FetchStatisticHandlerInterface|MockObject */
    private $fetchStatisticHandler;

    /** @var LoggerInterface|MockObject */
    private $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityMangerMock = $this->createMock(EntityManagerInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->validatorMock
            ->method('validate')
            ->willReturn($this->createMock(ConstraintViolationListInterface::class));
        $this->fetchStatisticHandler = $this->createMock(FetchStatisticHandlerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testNewInstance()
    {
        $walletService = new WalletService(
            $this->entityMangerMock,
            $this->validatorMock,
            $this->fetchStatisticHandler,
            $this->logger
        );
        $this->assertInstanceOf(WalletService::class, $walletService);
    }

    public function addWalletDataProvider(): array
    {
        $user = new User();
        $currency = new Currency();
        $address = 'test_address';

        return [
            [$user, $currency, $address]
        ];
    }

    /**
     * @dataProvider  addWalletDataProvider
     */
    public function testAddNewWalletWithCurrencyAsObject(User $user, Currency $currency, string $address)
    {
        $wallet = new Wallet();
        $balanceLog = new BalanceLog();
        $balanceLog->setBalance(11);

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository
            ->expects($this->once())
            ->method('creatNewWallet')
            ->with($user, $currency, $address)
            ->willReturn($wallet);

        $em = $this->entityMangerMock;
        $em->expects($this->once())
            ->method('getRepository')
            ->with(Wallet::class)
            ->willReturn($walletRepository);
        $em->expects($this->once())
            ->method('persist')
            ->with($balanceLog);
        $em->expects($this->once())
            ->method('flush');

        $fetchStatisticHandler = $this->fetchStatisticHandler;
        $fetchStatisticHandler->expects($this->once())
            ->method('getBalanceByWallet')
            ->with($wallet)
            ->willReturn($balanceLog);

        $walletService = new WalletService(
            $em,
            $this->validatorMock,
            $this->fetchStatisticHandler,
            $this->logger
        );

        $wallet = $walletService->addWallet($user, $currency, $address);

        $this->assertInstanceOf(Wallet::class, $wallet);
    }

    public function testAddNewWalletWithCurrencyAsString()
    {
        $user = new User();
        $currencyCode = 'BTC';
        $currency = new Currency();
        $address = 'test_address';

        $wallet = new Wallet();
        $balanceLog = new BalanceLog();
        $balanceLog->setBalance(11);

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository
            ->expects($this->once())
            ->method('creatNewWallet')
            ->with($user, $currency, $address)
            ->willReturn($wallet);

        $currencyRepository = $this->createMock(CurrencyRepository::class);
        $currencyRepository
            ->expects($this->once())
            ->method('find')
            ->with($currencyCode)
            ->willReturn($currency);

        $em = $this->entityMangerMock;
        $em->expects($this->at(1))
            ->method('getRepository')
            ->with(Wallet::class)
            ->willReturn($walletRepository);
        $em->expects($this->once())
            ->method('persist')
            ->with($balanceLog);
        $em->expects($this->once())
            ->method('flush');
        $em->expects($this->at(0))
            ->method('getRepository')
            ->with(Currency::class)
            ->willReturn($currencyRepository);

        $fetchStatisticHandler = $this->fetchStatisticHandler;
        $fetchStatisticHandler->expects($this->once())
            ->method('getBalanceByWallet')
            ->with($wallet)
            ->willReturn($balanceLog);

        $walletService = new WalletService(
            $em,
            $this->validatorMock,
            $this->fetchStatisticHandler,
            $this->logger
        );

        $wallet = $walletService->addWallet($user, $currencyCode, $address);
        $this->assertInstanceOf(Wallet::class, $wallet);
    }

    public function testAddNewWalletNegativeCurrencyNotFoundException()
    {
        $user = new User();
        $currencyCode = 'BTC';
        $address = 'test_address';

        $currencyRepository = $this->createMock(CurrencyRepository::class);

        $em = $this->entityMangerMock;
        $em->expects($this->once())
            ->method('getRepository')
            ->with(Currency::class)
            ->willReturn($currencyRepository);
        $em->expects($this->never())
            ->method('persist');
        $em->expects($this->never())
            ->method('flush');

        $walletService = new WalletService(
            $em,
            $this->validatorMock,
            $this->fetchStatisticHandler,
            $this->logger
        );
        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage('Currency with code BTC not found');

        $walletService->addWallet($user, $currencyCode, $address);
        $this->assertTrue(false, 'We shouldn\'t check that');// shouldn't check this assert, throws exception above
    }

    public function testAddNewWalletNegativeValidationException()
    {
        $user = new User();
        $currency = new Currency();
        $address = 'test_address';
        $wallet = new Wallet();

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository
            ->expects($this->once())
            ->method('creatNewWallet')
            ->with($user, $currency, $address)
            ->willReturn($wallet);

        $em = $this->entityMangerMock;
        $em->expects($this->once())
            ->method('getRepository')
            ->with(Wallet::class)
            ->willReturn($walletRepository);
        $em->expects($this->never())
            ->method('persist');
        $em->expects($this->never())
            ->method('flush');

        $error = $this->createMock(ConstraintViolationList::class);
        $error->method('count')
            ->willReturn(1);
        $error->method('__toString')
            ->willReturn('test error');

        $validation = $this->createMock(ValidatorInterface::class);
        $validation
            ->expects($this->once())
            ->method('validate')
            ->with($wallet)
            ->willReturn($error);

        $walletService = new WalletService(
            $em,
            $validation,
            $this->fetchStatisticHandler,
            $this->logger
        );
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('test error');

        $walletService->addWallet($user, $currency, $address);
        $this->assertTrue(false, 'We shouldn\'t check that');// shouldn't check this assert, throws exception above
    }

    public function testAddNewWalletFetchBalanceException()
    {
        $user = new User();
        $currency = new Currency();
        $address = 'test_address';
        $wallet = new Wallet();

        $walletRepository = $this->createMock(WalletRepository::class);
        $walletRepository
            ->expects($this->once())
            ->method('creatNewWallet')
            ->with($user, $currency, $address)
            ->willReturn($wallet);

        $em = $this->entityMangerMock;
        $em->expects($this->once())
            ->method('getRepository')
            ->with(Wallet::class)
            ->willReturn($walletRepository);
        $em->expects($this->never())
            ->method('persist');
        $em->expects($this->never())
            ->method('flush');

        $fetchStatisticHandler = $this->fetchStatisticHandler;
        $fetchStatisticHandler->expects($this->once())
            ->method('getBalanceByWallet')
            ->with($wallet)
            ->willThrowException(new \Exception('test error'));

        $walletService = new WalletService(
            $em,
            $this->validatorMock,
            $fetchStatisticHandler,
            $this->logger
        );

        $this->expectException(SyncBalanceException::class);
        $this->expectExceptionMessage('Can\'t sync balance. Check wallet address.');

        $walletService->addWallet($user, $currency, $address);
    }
}
