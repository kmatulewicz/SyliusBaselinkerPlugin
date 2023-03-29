<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Service;

use SyliusBaselinkerPlugin\Entity\OrderInterface;

interface OrderStatusApplierInterface
{
    public function apply(OrderInterface $order, int $type, int $data): bool;
}
