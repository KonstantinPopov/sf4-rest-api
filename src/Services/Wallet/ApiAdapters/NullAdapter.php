<?php

namespace App\Services\Wallet\ApiAdapters;

use App\Entity\Wallet;
use App\Exception\WrongApiResponseException;
use GuzzleHttp\ClientInterface;

/**
 * Use this adapter in tests
 */
class NullAdapter // extends AbstractAdapter
{
    const SLUG = 'null-api-adapter';

    protected const ENDPOINT = '/get_balance/{ADDRESS}';

    /**
     * {@inheritDoc}
     */
    protected function getEndpointOptionsByWallet(Wallet $wallet): array
    {
        return [['{ADDRESS}'], [$wallet->getAddress()], ];
    }

    /**
     * {@inheritDoc}
     */
    protected function mappingResponseBalance($responseData)
    {
        if (is_null(1)) {
            throw new WrongApiResponseException('Wrong Response. Cant map response.');
        }

        return 0;
    }
}
