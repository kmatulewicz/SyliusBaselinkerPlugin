<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Service;

use SM\Factory\FactoryInterface;
use Sylius\Component\Core\OrderPaymentTransitions;
use Sylius\Component\Core\OrderShippingTransitions;
use Sylius\Component\Order\OrderTransitions;
use SyliusBaselinkerPlugin\Entity\OrderInterface;
use SyliusBaselinkerPlugin\Resolver\StatusResolverInterface;

class OrderStatusApplier implements OrderStatusApplierInterface
{
    private FactoryInterface $smFactory;

    private StatusResolverInterface $statusResolver;

    private bool $cancel_on_delete = false;

    public function __construct(
        FactoryInterface $smFactory,
        StatusResolverInterface $statusResolver,
        string $onDelete = '',
    ) {
        $this->smFactory = $smFactory;
        $this->statusResolver = $statusResolver;
        $this->cancel_on_delete = ('cancel' === $onDelete) ? true : false;
    }

    public function apply(OrderInterface $order, int $type, int $data): bool
    {
        switch ($type) {
            case 4:
                return $this->applyOrderDeleted($order);
            case 18:
                return $this->applyOrderStatusChange($order, $data);
        }

        return false;
    }

    protected function applyOrderStatusChange(OrderInterface $order, int $data): bool
    {
        $newStatus = $this->statusResolver->resolveShopStatus($data);

        switch ($newStatus) {
            case 'cancelled':
                return $this->applyOrderCancel($order);
            case 'fulfilled':
                return $this->applyOrderFulfill($order);
        }

        return false;
    }

    protected function applyOrderCancel(OrderInterface $order): bool
    {
        $smInterface = $this->smFactory->get($order, OrderTransitions::GRAPH);
        if ($smInterface->can(OrderTransitions::TRANSITION_CANCEL)) {
            return $smInterface->apply(OrderTransitions::TRANSITION_CANCEL);
        }

        return false;
    }

    protected function applyOrderFulfill(OrderInterface $order): bool
    {
        $result = false;
        $smInterface = $this->smFactory->get($order, OrderPaymentTransitions::GRAPH);
        if ($smInterface->can(OrderPaymentTransitions::TRANSITION_PAY)) {
            $result = ($smInterface->apply(OrderPaymentTransitions::TRANSITION_PAY)) ? true : $result;
        }

        $smInterface = $this->smFactory->get($order, OrderShippingTransitions::GRAPH);
        if ($smInterface->can(OrderShippingTransitions::TRANSITION_SHIP)) {
            $result = ($smInterface->apply(OrderShippingTransitions::TRANSITION_SHIP)) ? true : $result;
        }

        return $result;
    }

    protected function applyOrderDeleted(OrderInterface $order): bool
    {
        if (false == $this->cancel_on_delete) {
            $order->setBaselinkerId(-1);

            return true;
        }

        return $this->applyOrderCancel($order);
    }
}
