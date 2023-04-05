<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Repository;

use SyliusBaselinkerPlugin\Entity\OrderInterface;

interface OrderRepositoryInterface
{
    public function find(int $id): ?OrderInterface;

    public function findNewOrdersToAdd(): array;
}
