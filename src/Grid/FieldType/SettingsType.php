<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Grid\FieldType;

use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\FieldTypes\FieldTypeInterface;
use SyliusBaselinkerPlugin\Entity\Settings;
use SyliusBaselinkerPlugin\Service\OrdersApiService;

class SettingsType implements FieldTypeInterface
{
    private array $sources = [];

    public function __construct(OrdersApiService $ordersApi)
    {
        $rawSources = $ordersApi->getOrderSources();
        /** @var array $value */
        foreach ($rawSources as $value) {
            $this->sources = $this->sources + $value;
        }
    }

    public function render(Field $field, $data, array $options = []): string
    {
        if (!($data instanceof Settings)) {
            return '';
        }

        if ($data->getName() === 'order.source') {
            $r = $value = $data->getValue();
            $r .= (array_key_exists($value, $this->sources) ? ' (' . (string) $this->sources[$value] . ')' : '');

            return htmlspecialchars($r);
        }

        return htmlspecialchars($data->getValue());
    }

    public function configureOptions(mixed $resolver): void
    {
    }

    public function getName(): string
    {
        return 'status_connected';
    }
}
