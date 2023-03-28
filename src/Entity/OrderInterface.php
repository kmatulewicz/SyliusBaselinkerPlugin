<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Entity;

use Sylius\Component\Core\Model\OrderInterface as ModelOrderInterface;

interface OrderInterface extends ModelOrderInterface
{
    public function getBaselinkerId(): int;

    public function setBaselinkerId(int $id): self;

    public function getBaselinkerUpdateTime(): int;

    public function setBaselinkerUpdateTime(int $time): self;
}
