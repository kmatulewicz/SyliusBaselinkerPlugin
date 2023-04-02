<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\DataProvider;

use SyliusBaselinkerPlugin\Entity\OrderInterface;

interface PaymentDataProviderInterface
{
    public function setOrder(OrderInterface $order): void;

    public function order_id(): int;

    public function payment_done(): float;

    public function payment_date(): int;

    public function payment_comment(): string;

    public function external_payment_id(): string;
}
