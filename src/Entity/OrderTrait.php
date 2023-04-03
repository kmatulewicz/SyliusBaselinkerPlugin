<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Entity;

use Doctrine\ORM\Mapping\Column;

trait OrderTrait
{
    /** @Column(name="baselinker_id", type="integer", options={"default":0}) */
    private int $baselinkerId = 0;

    /** @Column(name="baselinker_update_time", type="integer", options={"default":0}) */
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
