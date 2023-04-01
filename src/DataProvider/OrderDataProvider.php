<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\DataProvider;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use SyliusBaselinkerPlugin\Entity\Settings;
use SyliusBaselinkerPlugin\Entity\StatusesAssociations;

class OrderDataProvider implements OrderDataProviderInterface
{
    protected ?OrderInterface $order;

    protected ?CustomerInterface $customer;

    protected ?AddressInterface $deliveryAddress;

    protected ?AddressInterface $invoiceAddress;

    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->order = null;
        $this->customer = null;
        $this->deliveryAddress = null;
        $this->invoiceAddress = null;
    }

    public function setOrder(OrderInterface $order): void
    {
        $this->order = $order;
        $this->customer = $order->getCustomer();
        $this->deliveryAddress = $order->getShippingAddress();
        $this->invoiceAddress = $order->getBillingAddress();
    }

    public function order_status_id(): int
    {
        if (null === $this->order) {
            return 0;
        }
        /** @var StatusesAssociations|null $statusAssociation */
        $statusAssociation = $this->entityManager->find(StatusesAssociations::class, $this->order->getState());

        return (null === $statusAssociation) ? 0 : (int) $statusAssociation->getBaselinkerStatus();
    }

    public function custom_source_id(): int
    {
        /** @var Settings|null $orderSource */
        $orderSource = $this->entityManager->find(Settings::class, 'order.source');

        return (null === $orderSource) ? 0 : (int) $orderSource->getValue();
    }

    public function date_add(): int
    {
        if (null === $this->order) {
            return time();
        }
        $date = $this->order->getCreatedAt();

        return (null === $date) ? time() : $date->getTimestamp();
    }

    public function currency(): string
    {
        if (null === $this->order) {
            return '';
        }

        return $this->order->getCurrencyCode() ?? '';
    }

    public function payment_method(): string
    {
        if (null === $this->order) {
            return '';
        }
        /** @var PaymentInterface|false $payment */
        $payment = $this->order->getPayments()->last();
        if (false == $payment) {
            return '';
        }
        $method = $payment->getMethod();
        if (null === $method) {
            return '';
        }

        return $method->getName() ?? '';
    }

    public function payment_method_cod(): bool
    {
        /** @todo Business logic for payment_method_cod() */
        return false;
    }

    public function paid(): bool
    {
        if (null === $this->order) {
            return false;
        }

        return ('paid' === $this->order->getPaymentState()) ? true : false;
    }

    public function user_comments(): string
    {
        if (null === $this->order) {
            return '';
        }

        return $this->order->getNotes() ?? '';
    }

    public function admin_comments(): string
    {
        /** @todo Business logic for admin_comments() */
        return '';
    }

    public function email(): string
    {
        if (null === $this->customer) {
            return '';
        }

        return $this->customer->getEmail() ?? '';
    }

    public function phone(): string
    {
        if (null === $this->customer) {
            return '';
        }

        return $this->customer->getPhoneNumber() ?? '';
    }

    public function user_login(): string
    {
        /** @todo Business logic for user_login() */
        return '';
    }

    public function delivery_method(): string
    {
        if (null === $this->order) {
            return '';
        }
        /** @var ShipmentInterface|false $shipment */
        $shipment = $this->order->getShipments()->last();
        if (false == $shipment) {
            return '';
        }

        $method = $shipment->getMethod();
        if (null === $method) {
            return '';
        }

        return $method->getName() ?? '';
    }

    public function delivery_price(): float
    {
        if (null === $this->order) {
            return 0.0;
        }

        return $this->order->getShippingTotal() / 100;
    }

    public function delivery_fullname(): string
    {
        if (null === $this->deliveryAddress) {
            return '';
        }

        return $this->deliveryAddress->getFullName() ?? '';
    }

    public function delivery_company(): string
    {
        if (null === $this->deliveryAddress) {
            return '';
        }

        return $this->deliveryAddress->getCompany() ?? '';
    }

    public function delivery_address(): string
    {
        if (null === $this->deliveryAddress) {
            return '';
        }

        return $this->deliveryAddress->getStreet() ?? '';
    }

    public function delivery_postcode(): string
    {
        if (null === $this->deliveryAddress) {
            return '';
        }

        return $this->deliveryAddress->getPostcode() ?? '';
    }

    public function delivery_city(): string
    {
        if (null === $this->deliveryAddress) {
            return '';
        }

        return $this->deliveryAddress->getCity() ?? '';
    }

    public function delivery_state(): string
    {
        if (null === $this->deliveryAddress) {
            return '';
        }

        return $this->deliveryAddress->getProvinceName() ?? '';
    }

    public function delivery_country_code(): string
    {
        if (null === $this->deliveryAddress) {
            return '';
        }

        return $this->deliveryAddress->getCountryCode() ?? '';
    }

    public function delivery_point_id(): string
    {
        /** @todo Business logic for delivery_point_id() */
        return '';
    }

    public function delivery_point_name(): string
    {
        /** @todo Business logic for delivery_point_name() */
        return '';
    }

    public function delivery_point_address(): string
    {
        /** @todo Business logic for delivery_point_address() */
        return '';
    }

    public function delivery_point_postcode(): string
    {
        /** @todo Business logic for delivery_point_postcode() */
        return '';
    }

    public function delivery_point_city(): string
    {
        /** @todo Business logic for delivery_point_city() */
        return '';
    }

    public function invoice_fullname(): string
    {
        if (null === $this->invoiceAddress) {
            return '';
        }

        return $this->invoiceAddress->getFullName() ?? '';
    }

    public function invoice_company(): string
    {
        if (null === $this->invoiceAddress) {
            return '';
        }

        return $this->invoiceAddress->getCompany() ?? '';
    }

    public function invoice_nip(): string
    {
        /** @todo Business logic for invoice_nip() */
        return '';
    }

    public function invoice_address(): string
    {
        if (null === $this->invoiceAddress) {
            return '';
        }

        return $this->invoiceAddress->getStreet() ?? '';
    }

    public function invoice_postcode(): string
    {
        if (null === $this->invoiceAddress) {
            return '';
        }

        return $this->invoiceAddress->getPostcode() ?? '';
    }

    public function invoice_city(): string
    {
        if (null === $this->invoiceAddress) {
            return '';
        }

        return $this->invoiceAddress->getCity() ?? '';
    }

    public function invoice_state(): string
    {
        if (null === $this->invoiceAddress) {
            return '';
        }

        return $this->invoiceAddress->getProvinceName() ?? '';
    }

    public function invoice_country_code(): string
    {
        if (null === $this->invoiceAddress) {
            return '';
        }

        return $this->invoiceAddress->getCountryCode() ?? '';
    }

    public function want_invoice(): bool
    {
        /** @todo Business logic for want_invoice() */
        return false;
    }

    public function extra_field_1(): string
    {
        /** @todo Business logic for extra_field_1() */
        return '';
    }

    public function extra_field_2(): string
    {
        /** @todo Business logic for extra_field_2() */
        return '';
    }

    public function custom_extra_fields(): array
    {
        /** @todo Business logic for custom_extra_fields() */
        return [];
    }

    public function products(): array
    {
        return [];
    }
}
