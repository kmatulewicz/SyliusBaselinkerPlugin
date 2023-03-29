<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Services;

use SyliusBaselinkerPlugin\Entity\OrderInterface;

interface BaselinkerOrderStatusApplierInterface
{
    public function apply(OrderInterface $order, int $type, int $data): bool;
}
