<?php

namespace Tests\SyliusBaselinkerPlugin\Unit\DataProvider;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxRateInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolverInterface;
use SyliusBaselinkerPlugin\DataProvider\OrderItemDataProvider;
use SyliusBaselinkerPlugin\DataProvider\OrderItemDataProviderInterface;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;

final class OrderItemDataProviderTest extends TestCase
{
    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderItemDataProvider
     */
    public function test__implements_order_item_data_provider_interface(): void
    {
        assertInstanceOf(OrderItemDataProviderInterface::class, $this->p());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderItemDataProvider
     */
    public function test_attributes(): void
    {
        assertEquals(
            'someAttribute',
            $this->p('getVariantName', 'someAttribute')->attributes(),
        );

        assertEquals('', $this->p()->attributes());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderItemDataProvider
     */
    public function test_ean(): void
    {
        assertEquals('', $this->p()->ean());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderItemDataProvider
     */
    public function test_location(): void
    {
        assertEquals('', $this->p()->location());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderItemDataProvider
     */
    public function test_name(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $product
            ->expects(self::once())
            ->method('getName')
            ->willReturn('someName');
        assertEquals('someName', $this->p('getProduct', $product)->name());

        assertEquals('', $this->p('getProduct', null)->name());

        assertEquals('', $this->p()->name());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderItemDataProvider
     */
    public function test_price_brutto(): void
    {
        assertEquals(101.50, $this->p('getDiscountedUnitPrice', 10150)->price_brutto());

        assertEquals(0.0, $this->p()->price_brutto());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderItemDataProvider
     */
    public function test_product_id(): void
    {
        $variant = $this->createMock(ProductVariantInterface::class);
        $variant
            ->expects(self::once())
            ->method('getCode')
            ->willReturn('someCode');
        assertEquals('someCode', $this->p('getVariant', $variant)->product_id());

        assertEquals('', $this->p('getVariant', null)->product_id());

        assertEquals('', $this->p()->product_id());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderItemDataProvider
     */
    public function test_quantity(): void
    {
        assertEquals(
            30,
            $this->p('getQuantity', 30)
                ->quantity(),
        );

        assertEquals(0, $this->p()->quantity());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderItemDataProvider
     */
    public function test_sku(): void
    {
        $variant = $this->createMock(ProductVariantInterface::class);
        $variant
            ->expects(self::once())
            ->method('getCode')
            ->willReturn('someCode');
        assertEquals('someCode', $this->p('getVariant', $variant)->sku());

        assertEquals('', $this->p('getVariant', null)->sku());

        assertEquals('', $this->p()->sku());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderItemDataProvider
     */
    public function test_storage(): void
    {
        assertEquals('db', $this->p()->storage());

    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderItemDataProvider
     */
    public function test_storage_id(): void
    {
        assertEquals(0, $this->p()->storage_id());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderItemDataProvider
     */
    public function test_tax_rate(): void
    {
        $taxRate = $this->createMock(TaxRateInterface::class);
        $taxRate
            ->expects(self::once())
            ->method('getAmountAsPercentage')
            ->willReturn(23.5);
        $taxResolver = $this->createMock(TaxRateResolverInterface::class);
        $taxResolver
            ->expects(self::once())
            ->method('resolve')
            ->willReturn($taxRate);
        $provider = new OrderItemDataProvider($taxResolver);
        $orderItem = $this->createMock(OrderItemInterface::class);
        $orderItem
            ->expects(self::exactly(2))
            ->method('getVariant')
            ->willReturn($this->createMock(ProductVariantInterface::class));
        $provider->setItem($orderItem);
        assertEquals(
            23.5,
            $provider->tax_rate()
        );

        $taxResolver = $this->createMock(TaxRateResolverInterface::class);
        $taxResolver
            ->expects(self::once())
            ->method('resolve')
            ->willReturn(null);
        $provider = new OrderItemDataProvider($taxResolver);
        $provider->setItem($orderItem);
        assertEquals(
            0,
            $provider->tax_rate()
        );

        assertEquals(0, $this->p()->tax_rate());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderItemDataProvider
     */
    public function test_variant_id(): void
    {
        assertEquals(0, $this->p()->variant_id());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderItemDataProvider
     */
    public function test_warehouse_id(): void
    {
        assertEquals(0, $this->p()->warehouse_id());
    }

    /**
     * @covers \SyliusBaselinkerPlugin\DataProvider\OrderItemDataProvider
     */
    public function test_weight(): void
    {
        $variant = $this->createMock(ProductVariantInterface::class);
        $variant
            ->expects(self::once())
            ->method('getWeight')
            ->willReturn(5.5);
        assertEquals(5.5, $this->p('getVariant', $variant)->weight());

        $variant = $this->createMock(ProductVariantInterface::class);
        $variant
            ->expects(self::once())
            ->method('getWeight')
            ->willReturn(null);
        assertEquals(0, $this->p('getVariant', $variant)->weight());

        assertEquals(0, $this->p('getVariant', null)->weight());

        assertEquals(0, $this->p()->weight());
    }

    /**
     * Generates OrderItemInterface mock, containing a method
     * which name is passed in $method, that return the result passed in $return.
     * The method need to be run once.
     *
     * @param ?string $method Method name
     * @param mixed $return Will return
     * @return OrderItemInterface
     */
    private function i(?string $method = null, mixed $return = null): OrderItemInterface
    {
        $orderItem = $this->createMock(OrderItemInterface::class);
        if (null !== $method) {
            $orderItem
                ->expects(self::once())
                ->method($method)
                ->willReturn($return);
        }

        return $orderItem;
    }

    /**
     * Generates OrderItemDataProvider with set OrderItemInterface mock,
     * containing a method which name is passed in $itemMethod,
     * that return the result passed in $itemReturn. The method
     * need to be run once.
     *
     * @param ?string $itemMethod OrderItem method name
     * @param mixed $itemReturn OrderItem method will return
     * @return OrderItemDataProviderInterface
     */
    private function p(?string $itemMethod = null, mixed $itemReturn = null): OrderItemDataProviderInterface
    {
        $taxResolver = $this->createMock(TaxRateResolverInterface::class);
        $provider = new OrderItemDataProvider($taxResolver);
        if (null !== $itemMethod) {
            $provider->setItem($this->i($itemMethod, $itemReturn));
        }

        return $provider;
    }
}
