<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Serializers;

use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Sylius\Component\Core\Model\Order;
use SyliusBaselinkerPlugin\Subscribers\BaselinkerEventSubscriber;
use SyliusBaselinkerPlugin\Subscribers\BaselinkerHandlerSubscriber;

class BaselinkerSerializer
{
    private BaselinkerHandlerSubscriber $handlerSubscriber;

    private BaselinkerEventSubscriber $eventSubscriber;

    private Serializer $serializer;

    public function __construct(
        BaselinkerHandlerSubscriber $handlerSubscriber,
        BaselinkerEventSubscriber $eventSubscriber,
        string $confPath,
    ) {
        $this->handlerSubscriber = $handlerSubscriber;
        $this->eventSubscriber = $eventSubscriber;

        $this->serializer = SerializerBuilder::create()->configureHandlers(
            function (HandlerRegistry $registry) {
                $registry->registerSubscribingHandler($this->handlerSubscriber);
            },
        )->addDefaultHandlers()->configureListeners(
            function (EventDispatcher $dispatcher) {
                $dispatcher->addSubscriber($this->eventSubscriber);
            },
        )->addMetadataDir($confPath)->build();
    }

    public function serializeOrder(Order $order): string
    {
        $orderArray = $this->toArray($order);

        $shipment = $order->getShipments()->first();
        $shipmentArray = $this->toArray($shipment);

        $deliveryAddressArray = $this->toArray($order->getShippingAddress());
        $deliveryAddressArray = $this->addPrefixToArrayKeys('delivery_', $deliveryAddressArray);

        $invoiceAddressArray = $this->toArray($order->getBillingAddress());
        $invoiceAddressArray = $this->addPrefixToArrayKeys('invoice_', $invoiceAddressArray);

        $fullOrderArray = array_merge($orderArray, $shipmentArray, $deliveryAddressArray, $invoiceAddressArray);
        $jsonContent = $this->serialize($fullOrderArray);

        return $jsonContent;
    }

    private function toArray(mixed $data): array
    {
        return $this->serializer->toArray($data, $this->getContext());
    }

    private function serialize(mixed $data): string
    {
        return $this->serializer->serialize($data, 'json');
    }

    private function getContext(): SerializationContext
    {
        $context = new SerializationContext();
        $context->setGroups(['bl']);

        return $context;
    }

    /**
     * @psalm-suppress MixedAssignment
     */
    private function addPrefixToArrayKeys(string $prefix, array $array): array
    {
        $outputArray = [];
        foreach ($array as $key => $value) {
            $outputArray[$prefix . (string) $key] = $value;
        }

        return $outputArray;
    }
}
