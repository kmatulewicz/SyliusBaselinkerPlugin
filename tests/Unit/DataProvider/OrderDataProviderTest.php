<?php

declare(strict_types=1);

namespace Tests\SyliusBaselinkerPlugin\Unit\DataProvider;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

final class OrderDataProviderTest extends KernelTestCase
{
    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function admin_comments_works(): void
    {
        assertEquals('', $this->p()->admin_comments());
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function currency_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function custom_extra_fields_works(): void
    {
        assertEquals([], $this->p()->custom_extra_fields());
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function custom_source_id_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function date_add_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function delivery_address_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function delivery_city_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function delivery_company_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function delivery_country_code_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function delivery_fullname_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function delivery_method_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function delivery_point_address_works(): void
    {
        assertEquals(
            '',
            $this->p()->delivery_point_address(),
        );
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function delivery_point_city_works(): void
    {
        assertEquals(
            '',
            $this->p()->delivery_point_city(),
        );
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function delivery_point_id_works(): void
    {
        assertEquals(
            '',
            $this->p()->delivery_point_id(),
        );
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function delivery_point_name_works(): void
    {
        assertEquals(
            '',
            $this->p()->delivery_point_name(),
        );
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function delivery_point_postcode_works(): void
    {
        assertEquals(
            '',
            $this->p()->delivery_point_postcode(),
        );
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function delivery_postcode_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function delivery_price_works(): void
    {
        assertEquals(
            100.5,
            $this->t('getShippingTotal', 10050)->delivery_price(),
        );

        $provider = new OrderDataProvider($this->createMock(EntityManagerInterface::class));
        assertEquals(0.0, $provider->delivery_price());
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function delivery_state_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function email_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function extra_field_1_works(): void
    {
        assertEquals(
            '',
            $this->p()->extra_field_1(),
        );
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function extra_field_2_works(): void
    {
        assertEquals(
            '',
            $this->p()->extra_field_2(),
        );
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function invoice_address_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function invoice_city_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function invoice_company_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function invoice_country_code_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function invoice_fullname_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function invoice_nip_works(): void
    {
        assertEquals(
            '',
            $this->p()->invoice_nip(),
        );
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function invoice_postcode_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function invoice_state_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function order_date_provider_interface_is_implemented(): void
    {
        $this->assertInstanceOf(
            OrderDataProviderInterface::class,
            $this->p(),
            'Interface not implemented');
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function order_status_id_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function paid_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function payment_method_cod_works(): void
    {
        assertEquals(false, $this->p()->payment_method_cod());
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function payment_method_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function phone_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function products_works(): void
    {
        assertEquals([], $this->p()->products());
    }

    public function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function user_comments_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function user_login_works(): void
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
     * @test
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderDataProvider
     */
    public function want_invoice_works(): void
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
