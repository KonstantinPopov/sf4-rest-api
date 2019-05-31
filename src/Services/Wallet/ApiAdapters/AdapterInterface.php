<?php

namespace App\Services\Wallet\ApiAdapters;

use App\Entity\Wallet;

interface AdapterInterface
{
    const SLUG = 'adapter-interface';

    public function fetchBalance(Wallet $wallet): float;
}
