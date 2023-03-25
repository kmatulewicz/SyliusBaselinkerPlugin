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
use SyliusBaselinkerPlugin\Entity\BaselinkerSettings;
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
                'event' => 'serializer.post_serialize',
                'method' => 'getOrderSource',
                'class' => 'Sylius\\Component\\Core\\Model\\Order',
            ],
            [
                'event' => 'serializer.post_serialize',
                'method' => 'getDummyMissingOrderProperties',
                'class' => 'Sylius\\Component\\Core\\Model\\Order',
            ],
            [
                'event' => 'serializer.post_serialize',
                'method' => 'getDummyMissingOrderItemProperties',
                'class' => 'Sylius\\Component\\Core\\Model\\OrderItem',
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

    public function getOrderSource(ObjectEvent $event): void
    {
        /** @var BaselinkerSettings|null $orderSourceSetting */
        $orderSourceSetting = $this->entityManager->find(BaselinkerSettings::class, 'orderSource');
        if (null === $orderSourceSetting) {
            throw new Exception('Setting not found');
        }
        $orderSource = $orderSourceSetting->getValue();

        $visitor = $event->getVisitor();
        if (!($visitor instanceof JsonSerializationVisitor)) {
            throw new Exception('Wrong visitor');
        }
        $visitor->visitProperty(new StaticPropertyMetadata('', 'custom_source_id', $orderSource), $orderSource);
    }

    public function getDummyMissingOrderProperties(ObjectEvent $event): void
    {
        $visitor = $event->getVisitor();
        if (!($visitor instanceof JsonSerializationVisitor)) {
            throw new Exception('Wrong visitor');
        }
        $visitor->visitProperty(new StaticPropertyMetadata('', 'payment_method_cod', '0'), '0');
        $visitor->visitProperty(new StaticPropertyMetadata('', 'admin_comments', ''), '');
        $visitor->visitProperty(new StaticPropertyMetadata('', 'delivery_point_id', ''), '');
        $visitor->visitProperty(new StaticPropertyMetadata('', 'delivery_point_name', ''), '');
        $visitor->visitProperty(new StaticPropertyMetadata('', 'delivery_point_address', ''), '');
        $visitor->visitProperty(new StaticPropertyMetadata('', 'delivery_point_postcode', ''), '');
        $visitor->visitProperty(new StaticPropertyMetadata('', 'delivery_point_city', ''), '');
        $visitor->visitProperty(new StaticPropertyMetadata('', 'invoice_nip', ''), '');
        $visitor->visitProperty(new StaticPropertyMetadata('', 'want_invoice', '0'), '0');
        $visitor->visitProperty(new StaticPropertyMetadata('', 'extra_field_1', ''), '');
        $visitor->visitProperty(new StaticPropertyMetadata('', 'extra_field_2', ''), '');
        $visitor->visitProperty(new StaticPropertyMetadata('', 'custom_extra_fields', ''), '');
    }

    public function getDummyMissingOrderItemProperties(ObjectEvent $event): void
    {
        $visitor = $event->getVisitor();
        if (!($visitor instanceof JsonSerializationVisitor)) {
            throw new Exception('Wrong visitor');
        }
        $visitor->visitProperty(new StaticPropertyMetadata('', 'storage', 'db'), 'db');
        $visitor->visitProperty(new StaticPropertyMetadata('', 'storage_id', '0'), '0');
        $visitor->visitProperty(new StaticPropertyMetadata('', 'product_id', ''), '');
        $visitor->visitProperty(new StaticPropertyMetadata('', 'variant_id', ''), '');
        $visitor->visitProperty(new StaticPropertyMetadata('', 'ean', ''), '');
        $visitor->visitProperty(new StaticPropertyMetadata('', 'location', ''), '');
        //warehouse_id
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
