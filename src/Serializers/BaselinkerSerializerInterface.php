<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Serializers;

use Sylius\Component\Core\Model\OrderInterface;

interface BaselinkerSerializerInterface
{
    public function serializeOrder(OrderInterface $order): array;
}
