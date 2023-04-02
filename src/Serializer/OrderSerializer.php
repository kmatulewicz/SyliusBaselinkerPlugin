<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Serializer;

use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use SyliusBaselinkerPlugin\DataProvider\OrderDataProviderInterface;
use SyliusBaselinkerPlugin\DataProvider\OrderItemDataProviderInterface;
use SyliusBaselinkerPlugin\DataProvider\PaymentDataProviderInterface;
use SyliusBaselinkerPlugin\Entity\OrderInterface;

class OrderSerializer implements OrderSerializerInterface
{
    private OrderDataProviderInterface $orderDataProvider;

    private OrderItemDataProviderInterface $orderItemDataProvider;

    private PaymentDataProviderInterface $paymentDataProvider;

    public function __construct(
        OrderDataProviderInterface $orderDataProvider,
        OrderItemDataProviderInterface $orderItemDataProvider,
        PaymentDataProviderInterface $paymentDataProvider,
    ) {
        $this->orderDataProvider = $orderDataProvider;
        $this->orderItemDataProvider = $orderItemDataProvider;
        $this->paymentDataProvider = $paymentDataProvider;
    }

    public function serializeOrder(OrderInterface $order): array
    {
        $dataKeys = [
            'order_status_id',
            'custom_source_id',
            'date_add',
            'currency',
            'payment_method',
            'payment_method_cod',
            'paid',
            'user_comments',
            'admin_comments',
            'email',
            'phone',
            'user_login',
            'delivery_method',
            'delivery_price',
            'delivery_fullname',
            'delivery_company',
            'delivery_address',
            'delivery_postcode',
            'delivery_city',
            'delivery_state',
            'delivery_country_code',
            'delivery_point_id',
            'delivery_point_name',
            'delivery_point_address',
            'delivery_point_postcode',
            'delivery_point_city',
            'invoice_fullname',
            'invoice_company',
            'invoice_nip',
            'invoice_address',
            'invoice_postcode',
            'invoice_city',
            'invoice_state',
            'invoice_country_code',
            'want_invoice',
            'extra_field_1',
            'extra_field_2',
            'custom_extra_fields',
            'products',
        ];

        $this->orderDataProvider->setOrder($order);
        $dataValues = array_map(function ($name): mixed {
            return call_user_func_array([$this->orderDataProvider, $name], []);
        }, $dataKeys);

        $combinedArray = array_combine($dataKeys, $dataValues);

        $orderItems = $order->getItems();
        $products = [];
        foreach ($orderItems as $item) {
            $products[] = $this->serializeOrderItem($item);
        }

        $adjustments = $order->getAdjustmentsRecursively(AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT);
        $aggregatedOrderPromotions = [];
        /** @var AdjustmentInterface $adjustment */
        foreach ($adjustments as $adjustment) {
            $label = $adjustment->getLabel();
            if (null === $label) {
                continue;
            }
            if (array_key_exists($label, $aggregatedOrderPromotions)) {
                $aggregatedOrderPromotions[$label] += $adjustment->getAmount();
            } else {
                $aggregatedOrderPromotions[$label] = $adjustment->getAmount();
            }
        }

        foreach ($aggregatedOrderPromotions as $label => $value) {
            $adjustmentItem = [
                'storage' => '',
                'storage_id' => 0,
                'product_id' => '',
                'variant_id' => 0,
                'name' => 'Order promotion: ' . $label,
                'sku' => '',
                'ean' => '',
                'location' => '',
                'warehouse_id' => 0,
                'attributes' => '',
                'price_brutto' => (float) ($value / 100),
                'tax_rate' => 0.0,
                'quantity' => 1,
                'weight' => 0.0,
            ];
            $products[] = $adjustmentItem;
        }

        $combinedArray['products'] = $products;

        return $combinedArray;
    }

    public function serializePayment(OrderInterface $order): array
    {
        $dataKeys = [
            'order_id',
            'payment_done',
            'payment_date',
            'payment_comment',
            'external_payment_id',
        ];

        $this->paymentDataProvider->setOrder($order);
        $dataValues = array_map(function ($name): mixed {
            return call_user_func_array([$this->paymentDataProvider, $name], []);
        }, $dataKeys);

        return array_combine($dataKeys, $dataValues);
    }

    protected function serializeOrderItem(OrderItemInterface $orderItem): array
    {
        $dataKeys = [
            'storage',
            'storage_id',
            'product_id',
            'variant_id',
            'name',
            'sku',
            'ean',
            'location',
            'warehouse_id',
            'attributes',
            'price_brutto',
            'tax_rate',
            'quantity',
            'weight',
        ];

        $this->orderItemDataProvider->setItem($orderItem);
        $dataValues = array_map(function ($name): mixed {
            return call_user_func_array([$this->orderItemDataProvider, $name], []);
        }, $dataKeys);

        return array_combine($dataKeys, $dataValues);
    }
}
