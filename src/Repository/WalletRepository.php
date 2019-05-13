<?php

namespace App\Repository;

use App\Entity\Currency;
use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Wallet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wallet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wallet[]    findAll()
 * @method Wallet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WalletRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    /**
     * @param User     $user
     * @param Currency $currency
     * @param string   $address
     *
     * @return Wallet
     * @throws \Doctrine\ORM\ORMException
     */
    public function creatNewWallet(User $user, Currency $currency, string $address)
    {
        $wallet = $this->findOneBy(['address' => $address]);

        if (!$wallet instanceof Wallet) {
            $wallet = new Wallet();
            $wallet->setBalance(0);
            $this->_em->persist($wallet);
        }

        if (!$wallet->getUsers()->contains($user)) {
            $wallet->addUser($user);
        }

        $wallet->setCurrency($currency);
        $wallet->setAddress($address);

        return $wallet;
    }

    public function findWalletsByUser(User $user)
    {
        return $this->createQueryBuilder('w')
            ->join('w.users', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getResult();
    }
}
