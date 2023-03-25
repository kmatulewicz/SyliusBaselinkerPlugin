<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Subscribers;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Proxy;
use Exception;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Taxation\Model\TaxRateInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolver;
use SyliusBaselinkerPlugin\Entity\BaselinkerStatusesAssociations;

class BaselinkerEventSubscriber implements EventSubscriberInterface
{
    private TaxRateResolver $taxRateResolver;

    private EntityManagerInterface $entityManager;

    public function __construct(TaxRateResolver $taxRateResolver, EntityManagerInterface $entityManager)
    {
        $this->taxRateResolver = $taxRateResolver;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            [
                'event' => 'serializer.post_serialize',
                'method' => 'getTaxRate',
                'class' => 'Sylius\\Component\\Core\\Model\\OrderItem',
            ],
            [
                'event' => 'serializer.post_serialize',
                'method' => 'getOrderStatus',
                'class' => 'Sylius\\Component\\Core\\Model\\Order',
            ],
            [
                'event' => 'serializer.pre_serialize',
                'method' => 'loadLazyClass',
            ],
        ];
    }

    public function getTaxRate(ObjectEvent $event): void
    {
        $orderItem = $event->getObject();
        if (!($orderItem instanceof OrderItem)) {
            throw new Exception('Not instance of Order class');
        }

        $variant = $orderItem->getVariant();
        if (!($variant instanceof ProductVariant)) {
            throw new Exception('Not instance of ProductVariant class');
        }

        $taxRate = $this->taxRateResolver->resolve($variant);
        if (!($taxRate instanceof TaxRateInterface)) {
            throw new Exception('No tax rates for variant');
        }

        $tax = $taxRate->getAmount();

        $visitor = $event->getVisitor();
        if (!($visitor instanceof JsonSerializationVisitor)) {
            throw new Exception('Wrong visitor');
        }
        $visitor->visitProperty(new StaticPropertyMetadata('', 'taxRate', $tax), $tax);
    }

    public function getOrderStatus(ObjectEvent $event): void
    {
        $object = $event->getObject();
        if (!($object instanceof Order)) {
            throw new Exception('Not instance of Order');
        }

        $orderState = $object->getState();
        /** @var BaselinkerStatusesAssociations|null $statusAssociation */
        $statusAssociation = $this->entityManager->find(BaselinkerStatusesAssociations::class, $orderState);
        var_dump($orderState);

        if (null === $statusAssociation) {
            throw new Exception('Status not found in associations');
        }
        $baselinkerStatus = $statusAssociation->getBaselinkerStatus();

        $visitor = $event->getVisitor();
        if (!($visitor instanceof JsonSerializationVisitor)) {
            throw new Exception('Wrong visitor');
        }
        $visitor->visitProperty(new StaticPropertyMetadata('', 'order_status_id', $baselinkerStatus), $baselinkerStatus);
    }

    public function loadLazyClass(PreSerializeEvent $event): void
    {
        /** @var mixed $object */
        $object = $event->getObject();

        if (is_object($object) && $object instanceof Proxy) {
            /** @var string|false $class */
            $class = get_parent_class($object);
            if ($class === false) {
                throw new Exception('No parent class in proxy');
            }
            $event->setType($class);
            $object->__load();
        }
    }
}
