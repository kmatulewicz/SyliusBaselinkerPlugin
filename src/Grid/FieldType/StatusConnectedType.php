<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Grid\FieldType;

use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\FieldTypes\FieldTypeInterface;
use SyliusBaselinkerPlugin\Entity\StatusesAssociations;
use SyliusBaselinkerPlugin\Service\OrdersApiService;

class StatusConnectedType implements FieldTypeInterface
{
    private array $statusList = [];

    public function __construct(OrdersApiService $ordersApi)
    {
        $this->statusList = $ordersApi->getOrderStatusList();
    }

    public function render(Field $field, $data, array $options = []): string
    {
        if (!($data instanceof StatusesAssociations)) {
            return '';
        }

        $name = '';
        $statusList = $this->statusList;
        $statusId = $data->getBaselinkerStatus();
        /** @var mixed $status */
        foreach ($statusList as $status) {
            if (is_array($status) && array_key_exists('id', $status) && (string) $status['id'] === $statusId) {
                $name = ' (' . (string) (($status['name']) ?? '') . ')';
            }
        }

        return htmlspecialchars($data->getBaselinkerStatus() . $name);
    }

    public function configureOptions(mixed $resolver): void
    {
    }

    public function getName(): string
    {
        return 'status_connected';
    }
}
