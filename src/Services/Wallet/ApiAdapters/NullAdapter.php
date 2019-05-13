<?php

namespace App\Services\Wallet\ApiAdapters;

use App\Entity\Wallet;
use Psr\Http\Message\ResponseInterface;

/**
 * Use this adapter in tests
 */
class NullAdapter //extends AbstractAdapter
{
    const SLUG = 'null-api-adapter';

    protected const ENDPOINT = '/get_balance/{ADDRESS}';

    /**
     * {@inheritDoc}
     */
    protected function getEndpointParametersByWallet(Wallet $wallet): array
    {
        return [['{ADDRESS}'], [$wallet->getAddress()], ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getBalanceFromResponse(ResponseInterface $response)
    {
        return 0;
    }
}
