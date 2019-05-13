<?php

namespace App\Services\Wallet;

use App\Entity\BalanceLog;
use App\Entity\Currency;
use App\Entity\User;
use App\Entity\Wallet;
use App\Exception\SyncBalanceException;
use App\Repository\CurrencyRepository;
use App\Repository\WalletRepository;
use App\Services\Wallet\ApiAdapters\AdapterChain;
use App\Services\Wallet\FetchStatisticHandlers\FetchStatisticHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WalletService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var ValidatorInterface */
    private $validator;

    /** @var FetchStatisticHandlerInterface */
    private $fetchStatisticHandler;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        FetchStatisticHandlerInterface $fetchStatisticHandler,
        LoggerInterface $logger
    )
    {
        $this->fetchStatisticHandler = $fetchStatisticHandler;
        $this->validator = $validator;
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param User            $user
     * @param Currency|string $currency
     * @param string          $address
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function addWallet(User $user, $currency, string $address)
    {
        if (!$currency instanceof Currency) {
            $currency = $this->getCurrencyRepository()->find($currency);
        }
        $wallet = $this->getWalletRepository()->creatNewWallet($user, $currency, $address);
        $this->validator->validate($wallet);
        try {
            $balance = $this->fetchBalance($wallet);
            $this->em->persist($balance);
        } catch (\Throwable $e) {
            throw new SyncBalanceException('Can\'t sync balance. Check wallet address.');
        }

        $this->em->flush();
    }

    /**
     * @param Wallet[] $wallets
     */
    public function syncBalance(Wallet $wallet = null)
    {
        $e = null;
        if ($wallet instanceof Wallet) {
            $wallets[] = $wallet;
        } else {
            $wallets = $this->getWalletRepository()->findAll();
        }

        // TODO IMPLEMENT BATCH SYNCs
        foreach ($wallets as $wallet) {
            try {
                $balance = $this->fetchBalance($wallet);
                $this->em->persist($balance);
            } catch (\Throwable $e) {
                $this->logger->error('Got error during fetch statistic', [
                    'wallet' => $wallet->__toString(),
                    'error' => $e->getMessage(),
                ]);
            }

        $this->em->flush();
        if ($e instanceof \Throwable) {
            throw new SyncBalanceException('Some wallets wasn\'t synced');
        }
    }}

    protected function fetchBalance(Wallet $wallet): BalanceLog
    {
        return $this->fetchStatisticHandler->getBalanceByWallet($wallet);
    }

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function getWalletsByUser(User $user)
    {
        return $this->getWalletRepository()->findWalletsByUser($user);
    }

    /**
     * @return WalletRepository
     */
    private function getWalletRepository(): WalletRepository
    {
        return $this->em->getRepository(Wallet::class);
    }

    /**
     * @return CurrencyRepository
     */
    private function getCurrencyRepository(): Currency
    {
        return $this->em->getRepository(Currency::class);
    }
}
