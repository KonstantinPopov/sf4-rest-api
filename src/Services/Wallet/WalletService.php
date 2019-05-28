<?php

namespace App\Services\Wallet;

use App\Entity\BalanceLog;
use App\Entity\Currency;
use App\Entity\User;
use App\Entity\Wallet;
use App\Exception\SyncBalanceException;
use App\Repository\CurrencyRepository;
use App\Repository\WalletRepository;
use App\Services\Wallet\FetchStatisticHandlers\FetchStatisticHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
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
     * @param Currency|string $currency Currency or Currency code
     * @param string          $address
     *
     * @return Wallet
     * @throws \Doctrine\ORM\ORMException
     */
    public function addWallet(User $user, $currency, string $address): Wallet
    {
        if (is_string($currency)) {
            $currencyCode = $currency;
            $currency = $this->getCurrencyRepository()->find($currency);
        }

        if (!$currency instanceof Currency) {
            throw new EntityNotFoundException(sprintf('Currency with code %s not found', $currencyCode ?? ''));
        }

        $wallet = $this->getWalletRepository()->creatNewWallet($user, $currency, $address);
        $errors = $this->validator->validate($wallet);
        if ($errors->count()) {
            throw new ValidatorException($errors->__toString());
        }

        try {
            $balance = $this->fetchBalance($wallet);
            $this->em->persist($balance);
        } catch (\Throwable $e) {
            throw new SyncBalanceException('Can\'t sync balance. Check wallet address.');
        }

        $this->em->flush();

        return $wallet;
    }

    /**
     * @param Wallet|null $wallet
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
        }
    }

    /**
     * @param Wallet $wallet
     *
     * @return BalanceLog
     */
    protected function fetchBalance(Wallet $wallet): BalanceLog
    {
        return $this->fetchStatisticHandler->getBalanceByWallet($wallet);
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
    private function getCurrencyRepository(): CurrencyRepository
    {
        return $this->em->getRepository(Currency::class);
    }
}
