<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;


/**
 * @ORM\Entity(repositoryClass="App\Repository\WalletRepository")
 * @ORM\HasLifecycleCallbacks
 * @Serializer\ExclusionPolicy("ALL")
 */
class Wallet
{
    use TimestampableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="wallets")
     * @ORM\JoinTable(name="users_wallets")
     * @var User[]
     * @Serializer\Exclude()
     */
    private $users;

    /**
     * @var Currency
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Currency")
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="code")
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"wallet:show", "wallet:list"})
     */
    private $currency;

    /**
     * @ORM\Column(type="decimal", precision=30, scale=18)
     *
     * @Serializer\Expose()
     * @Serializer\Type("float")
     * @Serializer\Groups({"wallet:show", "wallet:list"})
     *
     * @var float
     */
    private $balance;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Serializer\Expose()
     * @Serializer\Type("DateTime")
     * @Serializer\Groups({"wallet:show", "wallet:list"})
     */
    protected $balanceChangedAt;

    /**
     * @var \Countable
     *
     * @ORM\OneToMany(targetEntity="App\Entity\BalanceLog", mappedBy="wallet")
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"wallet:show"})
     */
    private $balanceLog;

    /**
     * @ORM\Column(type="string", unique=true)
     *
     * @Serializer\Expose()
     * @Serializer\Type("string")
     * @Serializer\Groups({"wallet:show", "wallet:list"})
     *
     * @var string
     */
    private $address;

    /**
     * {@inheritDoc}
     */
    public static function getDataStatisticAdapters()
    {
        return [];
    }

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->balanceLog = new ArrayCollection();
        $this->balance = 0;
    }

    public function __toString(): string
    {
        return $this->getAddress();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param User[] $users
     */
    public function setUsers(array $users): void
    {
        $this->users = $users;
    }

    /**
     * @param User $user
     */
    public function addUser(User $user): void
    {
        $this->users[] = $user;
    }
    /**
     * @return float
     */
    public function getBalance(): float
    {
        return $this->balance;
    }

    /**
     * @param float $balance
     *
     * @throws \Exception
     */
    public function setBalance(float $balance): void
    {
        $this->balance = $balance;
        $this->setBalanceChangedAt(new \DateTime());
    }

    /**
     * @return \DateTime
     */
    public function getBalanceChangedAt(): \DateTime
    {
        return $this->balanceChangedAt;
    }

    /**
     * @param \DateTime $balanceChangedAt
     */
    public function setBalanceChangedAt(\DateTime $balanceChangedAt): void
    {
        $this->balanceChangedAt = $balanceChangedAt;
    }

    /**
     * @return mixed
     */
    public function getBalanceLog()
    {
        return $this->balanceLog;
    }

    /**
     * @param mixed $balanceLog
     */
    public function setBalanceLog($balanceLog): void
    {
        $this->balanceLog = $balanceLog;
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address): void
    {
        $this->address = $address;
    }

}
