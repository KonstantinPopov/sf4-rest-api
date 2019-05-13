<?php

namespace App\Services\Wallet\ApiAdapters;

use App\Entity\Wallet;

interface AdapterInterface
{
    public function fetchBalance(Wallet $wallet): float;
}