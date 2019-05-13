<?php

namespace App\DataFixtures;

use App\Entity\Currency;
use App\Entity\User;
use App\Entity\Wallet;
use App\Services\Wallet\ApiAdapters\ChainSoAdapter;
use App\Services\Wallet\ApiAdapters\EtherscanAdapter;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class AppFixtures extends Fixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        if('dev' !== $_ENV['APP_ENV'] ?? null) {
            return;
        }

        /** Users */
        $user1 = new User();
        $user1->setEmail('admin@mail.com');
        $user1->setApiToken('1234567890');
        $user1->setEnabled(true);
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('dev@mail.com');
        $user2->setApiToken('111');
        $user2->setEnabled(true);
        $manager->persist($user2);

        /** Currencies */
        $currency1 = new Currency();
        $currency1->setCode('BTC');
        $currency1->setName('Bitcoin');
        $currency1->setApiAdapterSlug(ChainSoAdapter::SLUG);
        $manager->persist($currency1);

        $currency2 = new Currency();
        $currency2->setCode('LTC');
        $currency2->setName('Litecoin');
        $currency2->setApiAdapterSlug(ChainSoAdapter::SLUG);
        $manager->persist($currency2);

        $currency3 = new Currency();
        $currency3->setCode('ETH');
        $currency3->setName('Ethereum');
        $currency3->setApiAdapterSlug(EtherscanAdapter::SLUG);
        $manager->persist($currency3);

        /** Wallets */
        $wallet1 = new Wallet();
        $wallet1->setCurrency($currency1);
        $wallet1->setAddress('385cR5DM96n1HvBDMzLHPYcw89fZAXULJP');
        $wallet1->addUser($user1);
        $wallet1->addUser($user2);
        $wallet1->setBalance(0);
        $manager->persist($wallet1);

        $wallet2 = new Wallet();
        $wallet2->setCurrency($currency2);
        $wallet2->setAddress('3CDJNfdWX8m2NwuGUV3nhXHXEeLygMXoAj');
        $wallet2->addUser($user1);
        $wallet2->addUser($user2);
        $wallet2->setBalance(0);
        $manager->persist($wallet2);

        $wallet3 = new Wallet();
        $wallet3->setCurrency($currency3);
        $wallet3->setAddress('0xEA674fdDe714fd979de3EdF0F56AA9716B898ec8');
        $wallet3->addUser($user1);
        $wallet3->addUser($user2);
        $wallet3->setBalance(0);
        $manager->persist($wallet3);

        $manager->flush();
    }
}
