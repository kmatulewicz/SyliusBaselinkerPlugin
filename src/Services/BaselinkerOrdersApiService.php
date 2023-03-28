<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Services;

define('ALL_LOGS_TYPES', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20]);

use Exception;
use Sylius\Component\Core\Model\OrderInterface;
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

        array_walk($response, function ($entry, $logTypes) {
            if (false === $this->isValidJournalEntry($entry, $logTypes)) {
                throw new Exception('Journal has invalid entry');
            }
        });

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

    public function addOrder(OrderInterface $order): int
    {
        $serializedOrder = $this->serializer->serializeOrder($order);
        $response = $this->apiRequest->do(__FUNCTION__, $serializedOrder);
        if (!array_key_exists('order_id', $response)) {
            throw new Exception('No order_id in addOrder');
        }

        return (int) $response['order_id'];
    }

    private function isValidJournalEntry(array $entry, array $types = ALL_LOGS_TYPES): bool
    {
        if (!array_key_exists('id', $entry) or !is_int($entry['id'])) {
            return false;
        }
        if (!array_key_exists('order_id', $entry) or !is_int($entry['order_id'])) {
            return false;
        }
        if (!array_key_exists('log_type', $entry) or !is_int($entry['log_type'])) {
            return false;
        }
        if (!in_array($entry['log_type'], $types, true)) {
            return false;
        }
        switch ($entry['log_type']) {
            case 5:
            case 6:
            case 7:
            case 9:
            case 10:
            case 14:
            case 17:
            case 18:
                if (!array_key_exists('object_id', $entry) or !is_int($entry['object_id'])) {
                    return false;
                }
        }
        if (!array_key_exists('date', $entry) or !is_int($entry['date'])) {
            return false;
        }

        return true;
    }
}
