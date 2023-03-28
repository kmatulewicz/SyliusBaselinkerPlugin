<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Services;

define('ALL_LOGS_TYPES', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20]);

use Exception;
use Sylius\Component\Core\Model\Order;
use SyliusBaselinkerPlugin\Serializers\BaselinkerSerializer;

class BaselinkerOrdersApiService implements BaselinkerOrdersApiServiceInterface
{
    private BaselinkerApiRequestService $apiRequest;

    private BaselinkerSerializer $serializer;

    public function __construct(BaselinkerApiRequestService $apiRequest, BaselinkerSerializer $serializer)
    {
        $this->apiRequest = $apiRequest;
        $this->serializer = $serializer;
    }

    public function getLastLogId(): ?int
    {
        $journalList = $this->getJournalList();
        $last = (array) end($journalList);
        if (array_key_exists('log_id', $last)) {
            $id = (int) $last['log_id'];

            return $id;
        }

        return null;
    }

    public function getJournalList(int $lastLogId = 0, array $logTypes = ALL_LOGS_TYPES, int $orderId = 0): array
    {
        $parameters = [];
        if (0 != $lastLogId) {
            $parameters['last_log_id'] = $lastLogId;
        }
        if (0 != $orderId) {
            $parameters['order_id'] = $orderId;
        }
        $parameters['logs_types'] = $logTypes;

        $content = $this->apiRequest->do(__FUNCTION__, $parameters);

        if (!array_key_exists('logs', $content)) {
            throw new Exception('No logs in getJournalList');
        }
        $response = (array) $content['logs'];

        return $response;
    }

    public function getOrderSources(): array
    {
        $content = $this->apiRequest->do(__FUNCTION__);

        if (!array_key_exists('sources', $content)) {
            throw new Exception('No sources in getOrderSources');
        }

        $response = (array) $content['sources'];

        return $response;
    }

    public function getOrderStatusList(): array
    {
        $content = $this->apiRequest->do(__FUNCTION__);

        if (!array_key_exists('statuses', $content)) {
            throw new Exception('No statuses in getOrderStatusList');
        }

        $response = (array) $content['statuses'];

        return $response;
    }

    public function addOrder(Order $order): int
    {
        $serializedOrder = $this->serializer->serializeOrder($order);
        $response = $this->apiRequest->do(__FUNCTION__, $serializedOrder);
        if (!array_key_exists('order_id', $response)) {
            throw new Exception('No order_id in addOrder');
        }

        return (int) $response['order_id'];
    }
}
