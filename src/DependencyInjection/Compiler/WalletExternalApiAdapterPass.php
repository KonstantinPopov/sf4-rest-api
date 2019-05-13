<?php

namespace App\DependencyInjection\Compiler;

use App\Services\Wallet\ApiAdapters\AdapterChain;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class WalletExternalApiAdapterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(AdapterChain::class)) {
            return;
        }

        $definition = $container->findDefinition(AdapterChain::class);
        $taggedServices = $container->findTaggedServiceIds(AdapterChain::TAG_NAME);

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addAdapter', [new Reference($id), constant($id.'::SLUG')]);
        }
    }
}