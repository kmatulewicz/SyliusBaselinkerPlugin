<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Services;

use Sylius\Component\Core\Model\OrderInterface;

interface BaselinkerOrdersApiServiceInterface
{
    public const ALL_LOGS_TYPES = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20];

    public function getLastLogId(): ?int;

    public function getJournalList(int $lastLogId = 0, array $logTypes = self::ALL_LOGS_TYPES, int $orderId = 0): array;

    public function getOrderSources(): array;

    public function getOrderStatusList(): array;

    public function addOrder(OrderInterface $order): int;
}
