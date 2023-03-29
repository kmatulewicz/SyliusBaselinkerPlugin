<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\DataProvider;

use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolverInterface;

class OrderItemDataProvider implements OrderItemDataProviderInterface
{
    private ?OrderItemInterface $orderItem;

    private ?ProductVariantInterface $variant;

    private ?ProductInterface $product;

    private TaxRateResolverInterface $taxResolver;

    public function __construct(TaxRateResolverInterface $taxResolver)
    {
        $this->taxResolver = $taxResolver;
        $this->orderItem = null;
        $this->variant = null;
        $this->product = null;
    }

    public function setItem(OrderItemInterface $orderItem): void
    {
        $this->orderItem = $orderItem;
        $this->variant = $orderItem->getVariant();
        $this->product = $orderItem->getProduct();
    }

    public function storage(): string
    {
        return 'db';
    }

    public function storage_id(): int
    {
        /** @todo: Baselinker storage associations in next version */
        return 0;
    }

    public function product_id(): string
    {
        if (null == $this->variant) {
            return '';
        }

        return $this->variant->getCode() ?? '';
    }

    public function variant_id(): int
    {
        return 0;
    }

    public function name(): string
    {
        if (null == $this->product) {
            return '';
        }

        return $this->product->getName() ?? '';
    }

    public function sku(): string
    {
        return $this->product_id();
    }

    public function ean(): string
    {
        /** @todo Business logic for ean() */
        return '';
    }

    public function location(): string
    {
        /** @todo Business logic for location() */
        return '';
    }

    public function warehouse_id(): int
    {
        /** @todo Business logic for warehouse_id() */
        return 0;
    }

    public function attributes(): string
    {
        if (null === $this->orderItem) {
            return '';
        }

        return $this->orderItem->getVariantName() ?? '';
    }

    public function price_brutto(): float
    {
        if (null === $this->orderItem) {
            return 0.0;
        }

        return $this->orderItem->getDiscountedUnitPrice() / 100;
    }

    public function tax_rate(): float
    {
        if (null === $this->variant) {
            return 0.0;
        }
        $taxRate = $this->taxResolver->resolve($this->variant);
        if (null === $taxRate) {
            return 0.0;
        }

        return $taxRate->getAmountAsPercentage();
    }

    public function quantity(): int
    {
        if (null === $this->orderItem) {
            return 0;
        }

        return $this->orderItem->getQuantity();
    }

    public function weight(): float
    {
        if (null === $this->variant) {
            return 0.0;
        }

        return $this->variant->getWeight() ?? 0.0;
    }
}
