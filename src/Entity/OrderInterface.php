<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Entity;

use DateTime;
use Sylius\Component\Order\Model\OrderInterface as ModelOrderInterface;

interface OrderInterface extends ModelOrderInterface
{
    public function getBaselinkerId(): ?int;

    public function setBaselinkerId(int $id): self;

    public function getBaselinkerUpdateTime(): ?DateTime;

    public function setBaselinkerUpdateTime(DateTime $dateTime): self;
}
