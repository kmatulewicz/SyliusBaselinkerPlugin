<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;

trait OrderTrait
{
    #[Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $baselinkerId = 0;

    #[Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $baselinkerUpdateTime = 0;

    public function getBaselinkerId(): int
    {
        return $this->baselinkerId;
    }

    public function setBaselinkerId(int $id): self
    {
        $this->baselinkerId = $id;

        return $this;
    }

    public function getBaselinkerUpdateTime(): int
    {
        return $this->baselinkerUpdateTime;
    }

    public function setBaselinkerUpdateTime(int $time): self
    {
        $this->baselinkerUpdateTime = $time;

        return $this;
    }
}
