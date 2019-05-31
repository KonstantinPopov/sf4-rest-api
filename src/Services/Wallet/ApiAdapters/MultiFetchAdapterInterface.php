<?php

namespace App\Services\Wallet\ApiAdapters;

interface MultiFetchAdapterInterface
{
    public function fetchBalances(array $wallets);
}
