<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Resolver;

interface BaselinkerStatusResolverInterface
{
    public function resolveShopStatus(int $baselinkerStatus): ?string;

    public function resolveBaselinkerStatus(string $orderState): ?int;
}
