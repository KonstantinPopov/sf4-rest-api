<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BalanceLogRepository")
 * @ORM\Table(name="balance_log")
 * @ORM\HasLifecycleCallbacks
 */
class BalanceLog
{
    use TimestampableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=30, scale=18)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"wallet:show"})
     */
    private $balance;

    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Wallet")
     * @ORM\JoinColumn(name="wallet_id", referencedColumnName="id")
     */
    private $wallet;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"wallet:show"})
     * @Serializer\SerializedName("date")
     */
    protected $updatedAt;


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param mixed $balance
     */
    public function setBalance($balance): void
    {
        $this->balance = $balance;
    }

    /**
     * @return Wallet
     */
    public function getWallet()
    {
        return $this->wallet;
    }

    /**
     * @param Wallet $wallet
     */
    public function setWallet($wallet): void
    {
        $this->wallet = $wallet;
    }

    /**
     * @ORM\PrePersist()
     */
    public function updateWalletBalance()
    {
        $this->getWallet()->setBalance($this->getBalance());
    }
}
