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

    public function __construct(
        FactoryInterface $smFactory,
        StatusResolverInterface $statusResolver,
    ) {
        $this->smFactory = $smFactory;
        $this->statusResolver = $statusResolver;
    }

    public function apply(OrderInterface $order, int $type, int $data): bool
    {
        switch ($type) {
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
                $this->applyOrderCancel($order);

                break;
            case 'fulfilled':
                $this->applyOrderFulfill($order);

                break;
        }

        return false;
    }

    protected function applyOrderCancel(OrderInterface $order): void
    {
        $smInterface = $this->smFactory->get($order, OrderTransitions::GRAPH);
        if ($smInterface->can(OrderTransitions::TRANSITION_CANCEL)) {
            $smInterface->apply(OrderTransitions::TRANSITION_CANCEL);
        }
    }

    protected function applyOrderFulfill(OrderInterface $order): void
    {
        $smInterface = $this->smFactory->get($order, OrderPaymentTransitions::GRAPH);
        if ($smInterface->can(OrderPaymentTransitions::TRANSITION_PAY)) {
            $smInterface->apply(OrderPaymentTransitions::TRANSITION_PAY);
        }

        $smInterface = $this->smFactory->get($order, OrderShippingTransitions::GRAPH);
        if ($smInterface->can(OrderShippingTransitions::TRANSITION_SHIP)) {
            $smInterface->apply(OrderShippingTransitions::TRANSITION_SHIP);
        }
    }
}
