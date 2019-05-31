<?php

namespace App\Services\Wallet\ApiAdapters;

use App\Entity\Wallet;
use App\Exception\WrongApiResponseException;

class ChainSoAdapter extends AbstractAdapter
{
    const SLUG = 'chain-so';

    protected const ENDPOINT = '/get_address_balance/{NETWORK}/{ADDRESS}';

    /**
     * {@inheritdoc}
     */
    protected function getEndpointOptionsByWallet(Wallet $wallet): array
    {
        return  [
            ['{NETWORK}', '{ADDRESS}'],
            [$wallet->getCurrency()->getCode(), $wallet->getAddress()],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function mappingResponseBalance($responseData)
    {
        if (is_null($balance = $responseData['data']['confirmed_balance'] ?? null)) {
            throw new WrongApiResponseException('Wrong Response. Cant map response.');
        }

        return $balance;
    }
}
