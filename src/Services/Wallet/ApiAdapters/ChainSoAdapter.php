<?php

namespace App\Services\Wallet\ApiAdapters;

use App\Entity\Wallet;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class ChainSoAdapter extends AbstractAdapter
{
    const SLUG = 'chain-so';

    protected const ENDPOINT = '/get_address_balance/{NETWORK}/{ADDRESS}';

    /**
     * {@inheritDoc}
     */
    protected function getEndpointOptionsByWallet(Wallet $wallet): array
    {
        return  [
            ['{NETWORK}', '{ADDRESS}'],
            [$wallet->getCurrency()->getCode(), $wallet->getAddress()]
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function mappingResponseBalance($responseData)
    {
        if (is_null($balance = $responseData['data']['confirmed_balance'] ?? null)) {
            throw new \RuntimeException('Wrong Response');
        }

        return $balance;
    }
}
