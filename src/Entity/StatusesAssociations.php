<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\NotNull;

#[Entity]
#[UniqueEntity(fields: 'shopStatus')]
#[UniqueEntity(fields: 'baselinkerStatus')]
#[Table('baselinker_statuses_associations')]
class StatusesAssociations implements ResourceInterface
{
    #[Id]
    #[Column(name: 'shop_status', type: Types::STRING)]
    #[NotNull]
    #[GeneratedValue('NONE')]
    private string $shopStatus = '';

    #[Column(name: 'baselinker_status', type: Types::STRING)]
    private string $baselinkerStatus = '';

    public function __construct(string $shopStatus = '', string $baselinkerStatus = '')
    {
        $this->setShopStatus($shopStatus);
        $this->setBaselinkerStatus($baselinkerStatus);
    }

    public function getId(): string
    {
        return $this->shopStatus;
    }

    /**
     * Get the value of shopStatus
     */
    public function getShopStatus(): string
    {
        return $this->shopStatus;
    }

    /**
     * Set the value of shopStatus
     */
    public function setShopStatus(string $shopStatus): self
    {
        $this->shopStatus = $shopStatus;

        return $this;
    }

    /**
     * Get the value of baselinkerStatus
     */
    public function getBaselinkerStatus(): string
    {
        return $this->baselinkerStatus;
    }

    /**
     * Set the value of baselinkerStatus
     */
    public function setBaselinkerStatus(string $baselinkerStatus): self
    {
        $this->baselinkerStatus = $baselinkerStatus;

        return $this;
    }
}
