<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Services;

use Sylius\Component\Core\Model\OrderInterface;

interface BaselinkerOrdersApiServiceInterface
{
    public function getLastLogId(): ?int;

    public function getJournalList(int $lastLogId, array $logTypes, int $orderId): array;

    public function getOrderSources(): array;

    public function getOrderStatusList(): array;

    public function addOrder(OrderInterface $order): int;
}
