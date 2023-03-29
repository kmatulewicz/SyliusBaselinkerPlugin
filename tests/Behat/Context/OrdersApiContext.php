<?php

declare(strict_types=1);

namespace Tests\SyliusBaselinkerPlugin\Behat\Context;

use Behat\Behat\Context\Context;
use Exception;
use SyliusBaselinkerPlugin\Serializer\OrderSerializerInterface;
use SyliusBaselinkerPlugin\Service\ApiRequestService;
use SyliusBaselinkerPlugin\Service\ApiRequestServiceInterface;
use SyliusBaselinkerPlugin\Service\OrdersApiService;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use function PHPUnit\Framework\assertIsInt;
use function PHPUnit\Framework\assertNull;

class OrdersApiContext  implements Context
{
    private ApiRequestServiceInterface $apiRequest;
    private OrdersApiService $orderApi;
    private mixed $result = "dummy";
    private OrderSerializerInterface $serializer;

    public function __construct(OrderSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Given Baselinker API is :arg1, token is :arg2 and number orders in Baselinker is :arg3
     */
    public function baselinkerApiIsTokenIsAndNumberOrdersInBaselinkerIs(string $arg1, string $arg2, string $arg3): void
    {
        $correctBody = '';
        if ($arg3 === '3') {
            $correctBody = '{
                "status": "SUCCESS",
                "logs": [
                {
                    "log_id": 456269,
                    "log_type": 13,
                    "order_id": 6911942,
                    "object_id": 0,
                    "date": 1516369287
                },
                {
                    "log_id": 456278,
                    "log_type": 7,
                    "order_id": 8911945,
                    "object_id": 5107899,
                    "date": 1516369390
                }
                ]
            }';
        } else {
            $correctBody = '{
                "status": "SUCCESS",
                "logs": []
            }';
        }
        $incorrectBody = '{
            "status": "ERROR",
            "error_code": "ERROR_EMPTY_TOKEN",
            "error_message": "No user token provided."
          }';
        $body = ($arg2 === 'correct') ? $correctBody : $incorrectBody;
        $response = ($arg1 === 'up') ? new MockResponse($body) : null;
        $this->apiRequest = new ApiRequestService(new MockHttpClient($response), $arg1, 'https://example.com', 'POST');

        $this->orderApi = new OrdersApiService($this->apiRequest, $this->serializer);
    }

    /**
     * @When I send query
     */
    public function iSendQuery(): void
    {
        try {
            $this->result = $this->orderApi->getLastLogId();
        } catch (Exception $e) {
            $this->result = $e;
        }
    }

    /**
     * @Then I should receive int
     */
    public function iShouldReceiveInt(): void
    {
        assertIsInt($this->result);
    }

    /**
     * @Then I should receive null
     */
    public function iShouldReceiveNull(): void
    {
        assertNull($this->result);
    }

    /**
     * @Then exception should be thrown
     */
    public function exceptionShouldBeThrown(): void
    {
        if (!($this->result instanceof Exception)) {
            throw new Exception('No exception');
        }
    }
}
