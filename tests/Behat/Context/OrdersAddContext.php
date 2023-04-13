<?php

declare(strict_types=1);

namespace Tests\SyliusBaselinkerPlugin\Behat\Context;

use Behat\Behat\Context\Context;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository;
use Sylius\Bundle\FixturesBundle\Loader\SuiteLoaderInterface;
use Sylius\Bundle\FixturesBundle\Suite\SuiteRegistryInterface;
use SyliusBaselinkerPlugin\Entity\OrderInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertGreaterThan;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertIsArray;
use function PHPUnit\Framework\assertIsString;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertTrue;

class OrdersAddContext extends KernelTestCase implements Context
{
    private array $addOrderArray = [
        "order_status_id" => 0,
        "custom_source_id" => 0,
        "currency" => "PLN",
        "payment_method" => "Płatność za pobraniem",
        "payment_method_cod" => false,
        "paid" => true,
        "user_comments" => "Notatka kupującege",
        "admin_comments" => "",
        "email" => "klient@example.com",
        "phone" => "0700 100 100",
        "user_login" => "Jan Kowalski",
        "delivery_method" => "Poczta",
        "delivery_price" => 9.99,
        "delivery_fullname" => "Katarzyna Kowalska",
        "delivery_company" => "Firma Krzak 2",
        "delivery_address" => "Piękna 3/2",
        "delivery_postcode" => "00-001",
        "delivery_city" => "Sosnowiec Dolny",
        "delivery_state" => "",
        "delivery_country_code" => "PL",
        "delivery_point_id" => "",
        "delivery_point_name" => "",
        "delivery_point_address" => "",
        "delivery_point_postcode" => "",
        "delivery_point_city" => "",
        "invoice_fullname" => "Jan Kowalski",
        "invoice_company" => "Firma Krzak",
        "invoice_nip" => "",
        "invoice_address" => "Piękna 3/1",
        "invoice_postcode" => "00-000",
        "invoice_city" => "Sosnowiec",
        "invoice_state" => "",
        "invoice_country_code" => "PL",
        "want_invoice" => false,
        "extra_field_1" => "",
        "extra_field_2" => "",
        "custom_extra_fields" => [],
        "products" => [
            [
                "storage" => "db",
                "storage_id" => 0,
                "product_id" => "pasztetowa-variant-0",
                "variant_id" => 0,
                "name" => "Pasztetowa",
                "sku" => "pasztetowa-variant-0",
                "ean" => "",
                "location" => "",
                "warehouse_id" => 0,
                "attributes" => "XXL",
                "price_brutto" => 100,
                "tax_rate" => 23,
                "quantity" => 3,
                "weight" => 0,

            ]
        ]
    ];
    private Application $app;
    private string $output = '';

    public function __construct()
    {
        $this->addOrderArray["date_add"] = strtotime('yesterday noon');
        $kernel = self::bootKernel();
        $this->app = new Application($kernel);
        parent::__construct();
    }

    /**
     * @Given /^there is a new order$/
     */
    public function thereIsANewOrder(): void
    {
        $fixtureRegister = self::getContainer()->get('sylius_fixtures.suite_registry');
        assertInstanceOf(SuiteRegistryInterface::class, $fixtureRegister);
        $fixtureLoader = self::getContainer()->get('sylius_fixtures.suite_loader');
        assertInstanceOf(SuiteLoaderInterface::class, $fixtureLoader);

        $suite = $fixtureRegister->getSuite('order');
        $fixtureLoader->load($suite);
    }

    /**
     * @Given /^I successfully run the command: "([^"]*)"$/
     */
    public function iSuccessfullyRunTheCommand(string $command): void
    {
        $apiMock = $this->createMock(HttpClientInterface::class);
        $apiMock
            ->expects(self::once())
            ->method('request')
            ->willReturnCallback([$this, 'checkAddOrder']);
        self::getContainer()->set(HttpClientInterface::class, $apiMock);
        $command = $this->app->find($command);
        $tester = new CommandTester($command);
        $tester->execute(['']);
        $this->output = $tester->getDisplay();
        $tester->assertCommandIsSuccessful();
    }

    /**
     * @Then /^the message should be displayed: "([^"]*)"$/
     */
    public function theMessageShouldBeDisplayed(string $message): void
    {
        assertStringContainsString($message, $this->output);
    }

    /**
     * @Given /^the Baselinker order number should be added to the order$/
     */
    public function theBaselinkerOrderNumberShouldBeAddedToTheOrder(): void
    {
        /** @var OrderRepository $repository */
        $repository = self::getContainer()->get('sylius.repository.order');
        $orders = $repository->findAll();
        assertCount(1, $orders);
        /** @var OrderInterface $order */
        $order = array_pop($orders);
        assertGreaterThan(0, $order->getBaselinkerId());
    }

    public function checkAddOrder(string $method, string $url, array $options): ResponseInterface
    {
        $this->checkRequest($method, $url, $options);
        $bodyArray = [];
        parse_str($options['body'], $bodyArray);
        assertArrayHasKey('method', $bodyArray);
        assertEquals('addOrder', $bodyArray['method']);
        assertArrayHasKey('parameters', $bodyArray);
        assertIsString($bodyArray['parameters']);
        $parameters = json_decode($bodyArray['parameters'], true);
        assertIsArray($parameters);
        assertTrue(
            $this->associativeArrayIsEqualRecursively($this->addOrderArray, $parameters),
            'Wrong parameters'
        );

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::once())->method('toArray')->willReturn(
            [
                "status" => "SUCCESS",
                "order_id" => "16331079",
            ]
        );
        return $response;
    }

    private function checkRequest(string $method, string $url, array $options): void
    {
        assertEquals('POST', $method, 'Wrong request method');
        assertEquals('https://api.baselinker.com/connector.php', $url, 'Wrong request url');
        assertArrayHasKey('headers', $options, 'No headers in request');
        assertArrayHasKey('X-BLToken', $options['headers'], 'No token header in request');
        assertEquals('change_me', $options['headers']['X-BLToken'], 'Wrong token in request');
        assertArrayHasKey('body', $options, 'Request does not have a body');
    }

    private function associativeArrayIsEqualRecursively(array $a1, array $a2): bool
    {
        if (count($a1) !== count($a2)) {
            return false;
        }
        foreach ($a1 as $key => $value) {
            if (!array_key_exists($key, $a2)) {
                return false;
            }
            if ($this->isAssociativeArray($value)) {
                if (false === $this->associativeArrayIsEqualRecursively($value, $a2[$key])) {
                    return false;
                }
            } elseif (is_array($value)) {
                if (false === $this->associativeArrayIsEqualRecursively($value, $a2[$key])) {
                    return false;
                }
            } else {
                if ($value !== $a2[$key]) {
                    return false;
                }
            }
        }
        return true;
    }

    private function isAssociativeArray(mixed $a): bool
    {
        if (!is_array($a)) {
            return false;
        }
        return (count(array_filter(array_keys($a), 'is_string')) === count($a));
    }
}
