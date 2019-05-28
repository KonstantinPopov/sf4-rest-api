<?php

namespace App\Services\Wallet\FetchStatisticHandlers;

use App\Entity\BalanceLog;
use App\Entity\Wallet;
use App\Exception\AdapterNotFoundException;
use App\Services\Wallet\ApiAdapters\AdapterChain;
use App\Services\Wallet\ApiAdapters\AdapterInterface;
use Psr\Log\LoggerInterface;

class FetchStatisticHandler implements FetchStatisticHandlerInterface
{
    /** @var AdapterChain  */
    private $apiAdapterChains;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(AdapterChain $adapterChain, LoggerInterface $logger)
    {
        $this->apiAdapterChains = $adapterChain;
        $this->logger = $logger;
    }

    /**
     * @param Wallet $wallet
     *
     * @return BalanceLog
     * @throws \Throwable
     */
    public function getBalanceByWallet(Wallet $wallet): BalanceLog
    {
        $currency = $wallet->getCurrency();
        $apiAdapterSlug = $currency->getApiAdapterSlug();
        $apiAdapter = $this->apiAdapterChains->getAdapter($apiAdapterSlug);

        if (!$apiAdapter instanceof AdapterInterface) {
            throw new AdapterNotFoundException(sprintf('Api adapter "%s" not supported', $apiAdapterSlug));
        }
        $balanceValue = $apiAdapter->fetchBalance($wallet);
        $this->logger->info('Fetch statistic', [
            'wallet' => $wallet->__toString(),
            'api_adapter' => $apiAdapter::SLUG,
        ]);

        $balance = new BalanceLog();
        $balance->setWallet($wallet);
        $balance->setBalance($balanceValue);

        return $balance;
    }
}
