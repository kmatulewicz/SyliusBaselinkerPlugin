<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\DataProvider;

use Sylius\Component\Core\Model\PaymentInterface;
use SyliusBaselinkerPlugin\Entity\OrderInterface;

class PaymentDataProvider implements PaymentDataProviderInterface
{
    private ?OrderInterface $order = null;

    private ?PaymentInterface $payment = null;

    public function setOrder(OrderInterface $order): void
    {
        $this->order = $order;
        $this->payment = $order->getLastPayment();
    }

    public function order_id(): int
    {
        return (null === $this->order) ? 0 : $this->order->getBaselinkerId();
    }

    public function payment_done(): float
    {
        if (null === $this->payment) {
            return 0.0;
        }
        if ('completed' !== $this->payment->getState()) {
            return 0.0;
        }

        return (float) (((int) $this->payment->getAmount()) / 100);
    }

    public function payment_date(): int
    {
        if (null === $this->payment) {
            return 0;
        }
        $updatedAt = $this->payment->getUpdatedAt();

        return (null === $updatedAt) ? 0 : $updatedAt->getTimestamp();
    }

    public function payment_comment(): string
    {
        if (null === $this->payment) {
            return '';
        }
        $method = $this->payment->getMethod();

        return (null === $method) ? '' : ($method->getName() ?? '');
    }

    public function external_payment_id(): string
    {
        if (null === $this->payment) {
            return '';
        }
        /** @var mixed $id */
        $id = $this->payment->getId();

        return (is_int($id)) ? (string) $id : '';
    }
}
