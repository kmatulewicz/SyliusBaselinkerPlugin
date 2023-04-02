<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Serializer;

use SyliusBaselinkerPlugin\Entity\OrderInterface;

interface OrderSerializerInterface
{
    public function serializeOrder(OrderInterface $order): array;

    public function serializePayment(OrderInterface $order): array;
}
