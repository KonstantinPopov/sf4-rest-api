<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CurrencyRepository")
 * @ORM\Table(name="currency")
 * @Serializer\ExclusionPolicy("ALL")
 */
class Currency
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=3)
     *
     * @Serializer\Groups({"wallet:list", "wallet:show"})
     * @Serializer\Expose
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Exclude()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=64)
     *
     * @Serializer\Exclude()
     */
    private $apiAdapterSlug;

    /**
     * @return string
     */
    public function getApiAdapterSlug()
    {
        return $this->apiAdapterSlug;
    }

    /**
     * @param mixed $apiAdapterSlug
     */
    public function setApiAdapterSlug($apiAdapterSlug): void
    {
        $this->apiAdapterSlug = $apiAdapterSlug;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = strtoupper($code);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
