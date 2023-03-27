<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;

trait OrderTrait
{
    #[Column(type: Types::INTEGER, nullable: true)]
    private ?int $baselinkerId = null;

    #[Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $baselinkerUpdateTime = null;

    public function getBaselinkerId(): ?int
    {
        return $this->baselinkerId;
    }

    public function setBaselinkerId(int $id): self
    {
        $this->baselinkerId = $id;

        return $this;
    }

    public function getBaselinkerUpdateTime(): DateTime
    {
        return $this->baselinkerUpdateTime;
    }

    public function setBaselinkerUpdateTime(DateTime $dateTime): self
    {
        $this->baselinkerUpdateTime = $dateTime;

        return $this;
    }
}
