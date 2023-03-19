<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Subscribers;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;

class BaselinkerHandlerSubscriber implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods(): array
    {
        return
            [
                [
                    'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                    'format' => 'json',
                    'type' => 'paymentState',
                    'method' => 'getPaymentState',
                ],
                [
                    'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                    'format' => 'json',
                    'type' => 'cash',
                    'method' => 'getCashInFloat',
                ],
            ];
    }

    public function getPaymentState(JsonSerializationVisitor $visitor, string $string, array $type): bool
    {
        return ($string === 'paid') ? true : false;
    }

    public function getCashInFloat(JsonSerializationVisitor $visitor, int $amount, array $type): float
    {
        return $amount / 100;
    }
}
