<?php

namespace App\Services\Wallet\ApiAdapters;

use App\Entity\Wallet;
use GuzzleHttp\ClientInterface;

class EtherscanAdapter extends AbstractAdapter
{
    const SLUG = 'ether-scan';

    protected const ENDPOINT = '/api?module=account&action=balance&address={ADDRESS}&apikey={API_KEY}';

    /** @var string */
    private $apiKey;

    public function __construct(ClientInterface $client, string $baseUrl, string $apiKey = '')
    {
        $this->apiKey = $apiKey;
        parent::__construct($client, $baseUrl);
    }

    /**
     * {@inheritDoc}
     */
    protected function getEndpointOptionsByWallet(Wallet $wallet): array
    {
        return  [
            ['{API_KEY}', '{ADDRESS}'],
            [$this->apiKey, $wallet->getAddress()]
        ];
    }

    protected function mappingResponseBalance($responseData)
    {
        if (is_null($balance = $responseData['result'] ?? null)) {
            throw new \RuntimeException('Wrong Response');
        }

        return $balance ? $balance/pow(10, 18) : 0;
    }
}
