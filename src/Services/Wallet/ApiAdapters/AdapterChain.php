<?php

namespace App\Services\Wallet\ApiAdapters;

class AdapterChain
{
    const TAG_NAME = 'app.wallet_external_api';
    /**
     * @var AdapterInterface[]
     */
    private $adapters = [];
    /**
     * @param AdapterInterface $adapter
     * @param string           $alias
     */
    public function addAdapter(AdapterInterface $adapter, string $alias)
    {
        $this->adapters[$alias] = $adapter;
    }

    /**
     * @param $alias
     *
     * @return AdapterInterface|null
     */
    public function getAdapter($alias)
    {
        if (array_key_exists($alias, $this->adapters)) {
            return $this->adapters[$alias];
        }

        return null;
    }

    /**
     * @return AdapterInterface[]
     */
    public function getAdapters()
    {
        return $this->adapters;
    }

}