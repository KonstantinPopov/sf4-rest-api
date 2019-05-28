<?php

namespace App\Services\Wallet\FetchStatisticHandlers;

use App\Entity\BalanceLog;
use App\Entity\Wallet;

interface FetchStatisticHandlerInterface
{
    public function getBalanceByWallet(Wallet $wallet): BalanceLog;
}