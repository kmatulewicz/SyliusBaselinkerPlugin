<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;

trait OrderTrait
{
    /** @Column(name="baselinker_id", type="integer", options={"default":0}) */
    #[Column(name: 'baselinker_id', type: Types::INTEGER, options: ['default' => 0])]
    private int $baselinkerId = 0;

    /** @Column(name="baselinker_update_time", type="datetime", nullable="true") */
    #[Column(name: 'baselinker_update_time', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $baselinkerUpdateTime = null;

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
        return $this->baselinkerUpdateTime->getTimestamp();
    }

    public function setBaselinkerUpdateTime(int $time): self
    {
        $new = new DateTime();
        $new->setTimestamp($time);
        $this->baselinkerUpdateTime = $new;

        return $this;
    }
}
