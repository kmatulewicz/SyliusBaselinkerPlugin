<?php

declare(strict_types=1);

namespace Tests\SyliusBaselinkerPlugin\Unit\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use SyliusBaselinkerPlugin\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    /** @test */
    public function it_sets_default_values(): void
    {
        $this->assertProcessedConfigurationEquals([], ['token' => '%env(string:BL_TOKEN)%'], 'token');
        $this->assertProcessedConfigurationEquals([], ['url' => 'https://api.baselinker.com/connector.php'], 'url');
        $this->assertProcessedConfigurationEquals([], ['method' => 'POST'], 'method');
        $this->assertProcessedConfigurationEquals([], ['on_delete' => 'unsync'], 'on_delete');
        $this->assertProcessedConfigurationEquals([], ['days_to_sync' => 14], 'days_to_sync');
        $this->assertProcessedConfigurationEquals([], ['max_orders_add' => 40], 'max_orders_add');
        $this->assertProcessedConfigurationEquals([], ['max_orders_payments' => 40], 'max_orders_payments');
    }

    /** @test */
    public function it_allows_to_set_other_values(): void
    {
        $this->assertProcessedConfigurationEquals([['token' => 'other_token']], ['token' => 'other_token'], 'token');
        $this->assertProcessedConfigurationEquals([['url' => 'https://other_url.php']], ['url' => 'https://other_url.php'], 'url');
        $this->assertProcessedConfigurationEquals([['method' => 'PUT']], ['method' => 'PUT'], 'method');
        $this->assertProcessedConfigurationEquals([['on_delete' => 'cancel']], ['on_delete' => 'cancel'], 'on_delete');
        $this->assertProcessedConfigurationEquals([['days_to_sync' => 7]], ['days_to_sync' => 7], 'days_to_sync');
        $this->assertProcessedConfigurationEquals([['max_orders_add' => 20]], ['max_orders_add' => 20], 'max_orders_add');
        $this->assertProcessedConfigurationEquals([['max_orders_payments' => 20]], ['max_orders_payments' => 20], 'max_orders_payments');
    }

    /** @test */
    public function it_does_not_allow_to_set_empty_values(): void
    {
        $this->assertConfigurationIsInvalid([['token' => '']]);
        $this->assertConfigurationIsInvalid([['url' => '']]);
        $this->assertConfigurationIsInvalid([['method' => '']]);
        $this->assertConfigurationIsInvalid([['on_delete' => '']]);
        $this->assertConfigurationIsInvalid([['days_to_sync' => '']]);
        $this->assertConfigurationIsInvalid([['max_orders_add' => '']]);
        $this->assertConfigurationIsInvalid([['max_orders_payments' => '']]);
    }

    /** @test */
    public function it_does_not_allow_to_set_out_of_range_values(): void
    {
        $this->assertConfigurationIsInvalid([['on_delete' => 'test']]);
        $this->assertConfigurationIsInvalid([['days_to_sync' => '100']]);
        $this->assertConfigurationIsInvalid([['days_to_sync' => '0']]);
        $this->assertConfigurationIsInvalid([['max_orders_add' => '0']]);
        $this->assertConfigurationIsInvalid([['max_orders_payments' => '0']]);
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
