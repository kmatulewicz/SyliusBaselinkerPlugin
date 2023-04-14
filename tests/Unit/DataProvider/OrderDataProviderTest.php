<?php

declare(strict_types=1);

namespace Tests\SyliusBaselinkerPlugin\Unit\DataProvider;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use SyliusBaselinkerPlugin\DataProvider\OrderDataProvider;
use SyliusBaselinkerPlugin\DataProvider\OrderDataProviderInterface;
use SyliusBaselinkerPlugin\Entity\OrderInterface;
use SyliusBaselinkerPlugin\Entity\Settings;
use SyliusBaselinkerPlugin\Entity\StatusesAssociations;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

final class OrderDataProviderTest extends TestCase
{
    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test__order_date_provider_interface_is_implemented(): void
    {
        $this->assertInstanceOf(
            OrderDataProviderInterface::class,
            $this->p(),
            'Interface not implemented');
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_admin_comments(): void
    {
        assertEquals('', $this->p()->admin_comments());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_currency(): void
    {
        assertEquals(
            'PLN',
            $this->t('getCurrencyCode', 'PLN')
                ->currency(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals(null, $provider->currency());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_custom_extra_fields(): void
    {
        assertEquals([], $this->p()->custom_extra_fields());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_custom_source_id(): void
    {
        $setting = $this->createMock(Settings::class);
        $setting
            ->expects(self::once())
            ->method('getValue')
            ->willReturn('101');

        $entity = $this->createMock(EntityManagerInterface::class);
        $entity->method('find')
            ->with(Settings::class, 'order.source')
            ->willReturn($setting);

        self::assertEquals(
            101,
            $this->p(null, $entity)
                ->custom_source_id(),
        );
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_date_add(): void
    {
        $dateTime = new DateTime('yesterday');
        assertEquals(
            $dateTime->getTimestamp(),
            $this->t('getCheckoutCompletedAt', $dateTime)
                ->date_add(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        $date = $provider->date_add();
        assertTrue(((time() - 3) < $date && $date < (time() + 3)));
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_delivery_address(): void
    {
        $shippingAddress = $this->createMock(AddressInterface::class);
        $shippingAddress
            ->expects(self::once())
            ->method('getStreet')
            ->willReturn('someAddress');
        $order = $this->o('getShippingAddress', $shippingAddress);

        $provider = $this->p($order);

        assertEquals(
            'someAddress',
            $provider->delivery_address(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->delivery_address());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_delivery_city(): void
    {
        $shippingAddress = $this->createMock(AddressInterface::class);
        $shippingAddress
            ->expects(self::once())
            ->method('getCity')
            ->willReturn('someCity');
        $order = $this->o('getShippingAddress', $shippingAddress);

        $provider = $this->p($order);

        assertEquals(
            'someCity',
            $provider->delivery_city(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->delivery_city());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_delivery_company(): void
    {
        $shippingAddress = $this->createMock(AddressInterface::class);
        $shippingAddress
            ->expects(self::once())
            ->method('getCompany')
            ->willReturn('someCompany');
        $order = $this->o('getShippingAddress', $shippingAddress);

        $provider = $this->p($order);

        assertEquals(
            'someCompany',
            $provider->delivery_company(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->delivery_company());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_delivery_country_code(): void
    {
        $shippingAddress = $this->createMock(AddressInterface::class);
        $shippingAddress
            ->expects(self::once())
            ->method('getCountryCode')
            ->willReturn('PL');
        $order = $this->o('getShippingAddress', $shippingAddress);

        $provider = $this->p($order);

        assertEquals(
            'PL',
            $provider->delivery_country_code(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->delivery_country_code());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_delivery_fullname(): void
    {
        $shippingAddress = $this->createMock(AddressInterface::class);
        $shippingAddress
            ->expects(self::once())
            ->method('getFullName')
            ->willReturn('Jan Kowalski');
        $order = $this->o('getShippingAddress', $shippingAddress);

        $provider = $this->p($order);

        assertEquals(
            'Jan Kowalski',
            $provider->delivery_fullname(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->delivery_fullname());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_delivery_method(): void
    {
        $method = $this->createMock(ShippingMethodInterface::class);
        $method
            ->expects(self::once())
            ->method('getName')
            ->willReturn('someName');
        $shipment = $this->createMock(ShipmentInterface::class);
        $shipment
            ->expects(self::once())
            ->method('getMethod')
            ->willReturn($method);
        $collection = new ArrayCollection([$shipment]);
        $order = $this->o('getShipments', $collection);

        $provider = $this->p($order);

        assertEquals(
            'someName',
            $provider->delivery_method(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->delivery_method());

        $collection = new ArrayCollection();
        $order = $this->o('getShipments', $collection);

        $provider = $this->p($order);

        assertEquals('', $provider->delivery_method(),
        );
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_delivery_point_address(): void
    {
        assertEquals(
            '',
            $this->p()->delivery_point_address(),
        );
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_delivery_point_city(): void
    {
        assertEquals(
            '',
            $this->p()->delivery_point_city(),
        );
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_delivery_point_id(): void
    {
        assertEquals(
            '',
            $this->p()->delivery_point_id(),
        );
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_delivery_point_name(): void
    {
        assertEquals(
            '',
            $this->p()->delivery_point_name(),
        );
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_delivery_point_postcode(): void
    {
        assertEquals(
            '',
            $this->p()->delivery_point_postcode(),
        );
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_delivery_postcode(): void
    {
        $address = $this->createMock(AddressInterface::class);
        $address
            ->expects(self::once())
            ->method('getPostcode')
            ->willReturn('00-000');
        $order = $this->o('getShippingAddress', $address);
        $provider = $this->p($order);

        assertEquals(
            '00-000',
            $provider->delivery_postcode(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->delivery_postcode());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_delivery_price(): void
    {
        assertEquals(
            100.5,
            $this->t('getShippingTotal', 10050)->delivery_price(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals(0.0, $provider->delivery_price());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_delivery_state(): void
    {
        $address = $this->createMock(AddressInterface::class);
        $address
            ->expects(self::once())
            ->method('getProvinceName')
            ->willReturn('someProvince');
        $order = $this->o('getShippingAddress', $address);
        $provider = $this->p($order);

        assertEquals(
            'someProvince',
            $provider->delivery_state(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->delivery_state());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_email(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer
            ->expects(self::once())
            ->method('getEmail')
            ->willReturn('some@email.com');

        assertEquals(
            'some@email.com',
            $this->t('getCustomer', $customer)->email(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->email());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_extra_field_1(): void
    {
        assertEquals(
            '',
            $this->p()->extra_field_1(),
        );
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_extra_field_2(): void
    {
        assertEquals(
            '',
            $this->p()->extra_field_2(),
        );
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_invoice_address(): void
    {
        $address = $this->createMock(AddressInterface::class);
        $address
            ->expects(self::once())
            ->method('getStreet')
            ->willReturn('someInvoiceStreet');
        $order = $this->o('getBillingAddress', $address);
        $provider = $this->p($order);

        assertEquals(
            'someInvoiceStreet',
            $provider->invoice_address(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->invoice_address());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_invoice_city(): void
    {
        $address = $this->createMock(AddressInterface::class);
        $address
            ->expects(self::once())
            ->method('getCity')
            ->willReturn('someInvoiceCity');
        $order = $this->o('getBillingAddress', $address);
        $provider = $this->p($order);

        assertEquals(
            'someInvoiceCity',
            $provider->invoice_city(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->invoice_city());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_invoice_company(): void
    {
        $address = $this->createMock(AddressInterface::class);
        $address
            ->expects(self::once())
            ->method('getCompany')
            ->willReturn('someInvoiceCompany');
        $order = $this->o('getBillingAddress', $address);
        $provider = $this->p($order);

        assertEquals(
            'someInvoiceCompany',
            $provider->invoice_company(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->invoice_company());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_invoice_country_code(): void
    {
        $address = $this->createMock(AddressInterface::class);
        $address
            ->expects(self::once())
            ->method('getCountryCode')
            ->willReturn('EN');
        $order = $this->o('getBillingAddress', $address);
        $provider = $this->p($order);

        assertEquals(
            'EN',
            $provider->invoice_country_code(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->invoice_country_code());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_invoice_fullname(): void
    {
        $address = $this->createMock(AddressInterface::class);
        $address
            ->expects(self::once())
            ->method('getFullName')
            ->willReturn('Katarzyna Kowalska');
        $order = $this->o('getBillingAddress', $address);
        $provider = $this->p($order);

        assertEquals(
            'Katarzyna Kowalska',
            $provider->invoice_fullname(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->invoice_fullname());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_invoice_nip(): void
    {
        assertEquals(
            '',
            $this->p()->invoice_nip(),
        );
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_invoice_postcode(): void
    {
        $address = $this->createMock(AddressInterface::class);
        $address
            ->expects(self::once())
            ->method('getPostcode')
            ->willReturn('01-001');
        $order = $this->o('getBillingAddress', $address);
        $provider = $this->p($order);

        assertEquals(
            '01-001',
            $provider->invoice_postcode(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->invoice_postcode());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_invoice_state(): void
    {
        $address = $this->createMock(AddressInterface::class);
        $address
            ->expects(self::once())
            ->method('getProvinceName')
            ->willReturn('someInvoiceState');
        $order = $this->o('getBillingAddress', $address);
        $provider = $this->p($order);

        assertEquals(
            'someInvoiceState',
            $provider->invoice_state(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->invoice_state());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_order_status_id(): void
    {
        $statusAssociation = $this->createMock(StatusesAssociations::class);
        $statusAssociation
            ->expects(self::once())
            ->method('getBaselinkerStatus')
            ->willReturn('100');

        $entity = $this->createMock(EntityManagerInterface::class);
        $entity
            ->expects(self::once())
            ->method('find')
            ->with(StatusesAssociations::class, 'state')
            ->willReturn($statusAssociation);

        assertEquals(
            100,
            $this->p(

                $this->o('getState', 'state'),
                $entity,
            )->order_status_id(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals(0, $provider->order_status_id());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_paid(): void
    {
        assertEquals(
            true,
            $this->t('getPaymentState', 'paid')
                ->paid(),
        );
        assertEquals(
            false,
            $this->t('getPaymentState', 'unpaid')
                ->paid(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals(false, $provider->paid());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_payment_method(): void
    {
        $method = $this->createMock(PaymentMethodInterface::class);
        $method
            ->expects(self::once())
            ->method('getName')
            ->willReturn('someMethod');
        $payment = $this->createMock(PaymentInterface::class);
        $payment->expects(self::once())
            ->method('getMethod')
            ->willReturn($method);
        $payments = new ArrayCollection([$payment]);
        assertEquals(
            'someMethod',
            $this->t('getPayments', $payments)
                ->payment_method(),
        );

        $payment = $this->createMock(PaymentInterface::class);
        $payment->expects(self::once())
            ->method('getMethod')
            ->willReturn(null);
        $payments = new ArrayCollection([$payment]);
        assertEquals(
            '',
            $this->t('getPayments', $payments)
                ->payment_method(),
        );

        $payments = new ArrayCollection();
        assertEquals(
            '',
            $this->t('getPayments', $payments)
                ->payment_method(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->payment_method());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_payment_method_cod(): void
    {
        assertEquals(false, $this->p()->payment_method_cod());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_phone(): void
    {
        $address = $this->createMock(CustomerInterface::class);
        $address
            ->expects(self::once())
            ->method('getPhoneNumber')
            ->willReturn('0700100200');
        $order = $this->o('getCustomer', $address);
        $provider = $this->p($order);
        assertEquals(
            '0700100200',
            $provider->phone(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->phone());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_products(): void
    {
        assertEquals([], $this->p()->products());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_user_comments(): void
    {
        assertEquals(
            'some comment',
            $this->t('getNotes', 'some comment')
                ->user_comments(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->user_comments());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_user_login(): void
    {
        $address = $this->createMock(CustomerInterface::class);
        $address
            ->expects(self::once())
            ->method('getFullName')
            ->willReturn('Karol Kowalski');
        $order = $this->o('getCustomer', $address);
        $provider = $this->p($order);
        assertEquals(
            'Karol Kowalski',
            $provider->user_login(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals('', $provider->user_login());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function test_want_invoice(): void
    {
        assertEquals(false, $this->p()->want_invoice());
    }

    /**
     * Generates OrderInterface mock, containing a method which name is passed in $method,
     * that return the result passed in $willReturn. The method need to be run once.
     *
     * @param string $method
     * @param mixed $willReturn
     * @return OrderInterface
     */
    private function o(string $method, mixed $willReturn): OrderInterface
    {
        $mock = $this->createMock(OrderInterface::class);
        $mock
            ->expects(self::once())
            ->method($method)
            ->willReturn($willReturn);
        return $mock;
    }

    /**
     * Generates OrderDataProvider with set OrderInterface mock and EntityManagerInterface mock,
     * passed in arguments. If null is passed then corresponding empty mock will be set.
     *
     * @param OrderInterface|null $order
     * @param EntityManagerInterface|null $entity
     * @return OrderDataProviderInterface
     */
    private function p(?OrderInterface $order = null, ?EntityManagerInterface $entity = null): OrderDataProviderInterface
    {
        if (null === $entity) {
            $entity = $this->createMock(EntityManagerInterface::class);
        }
        if (null === $order) {
            $order = $this->createMock(OrderInterface::class);
        }
        $provider = new OrderDataProvider($entity);
        $provider->setOrder($order);
        return $provider;
    }

    /**
     * Generates OrderDataProvider with set OrderInterface mock,
     * containing a method which name is passed in $orderMethod,
     * that return the result passed in $orderReturn. The method
     * need to be run once.
     *
     * @param string $orderMethod Name of method contained by OrderInterface mock
     * @param mixed $orderReturn Result returned by method contained by OrderInterface mock
     * @return OrderDataProviderInterface
     */
    private function t(string $orderMethod, mixed $orderReturn): OrderDataProviderInterface
    {
        $order = $this->o($orderMethod, $orderReturn);
        return $this->p($order);
    }
}
