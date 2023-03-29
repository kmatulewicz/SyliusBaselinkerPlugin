<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Serializer;

use Sylius\Component\Core\Model\OrderInterface;

interface OrderSerializerInterface
{
    public function serializeOrder(OrderInterface $order): array;
}
