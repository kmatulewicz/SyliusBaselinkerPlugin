<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\DataProvider;

use Sylius\Component\Core\Model\OrderItemInterface;

interface BaselinkerOrderItemDataProviderInterface
{
    public function setItem(OrderItemInterface $orderItem): void;

    public function storage(): string;

    public function storage_id(): int;

    public function product_id(): string;

    public function variant_id(): int;

    public function name(): string;

    public function sku(): string;

    public function ean(): string;

    public function location(): string;

    public function warehouse_id(): int;

    public function attributes(): string;

    public function price_brutto(): float;

    public function tax_rate(): float;

    public function quantity(): int;

    public function weight(): float;
}
